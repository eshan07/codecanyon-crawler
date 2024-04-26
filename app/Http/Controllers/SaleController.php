<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sale;
use App\Models\SaleHistory;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

class SaleController extends Controller
{
    public function index()
    {
//        $this->syncData();
        // Get date-wise sales from the sales table
        $sales = Sale::with('project')
            ->selectRaw('DATE(created_at) as date, SUM(sales_count) as sales_total, SUM(new_sale_count) as new_sale, SUM(refund_count) as refund_count' )
            ->groupBy('date')
            ->get();

        // Get date-wise total sales from the sale_history table
        $totalSalesHistory = SaleHistory::selectRaw('DATE(created_at) as date, SUM(total_sale) as total_sale')
            ->groupBy('date')
            ->get();


        // Merge the two collections based on the date
        $salesByDate = $sales->map(function ($sale) use ($totalSalesHistory, &$previousDaySalesTotal) {
            // Find the total sales for the current date from the sale history
            $totalSales = $totalSalesHistory->firstWhere('date', $sale->date);

            // Calculate the new sales count by comparing the current day's sales total with the previous day's sales total
            $newSalesCount = $previousDaySalesTotal ? $sale->sales_total - $previousDaySalesTotal : 0;

            // Update the previous day's sales total for the next iteration
            $previousDaySalesTotal = $sale->sales_total;

            return [
                'date' => $sale->date,
                'actual_sale' => $sale->sales_total,
                'total_sale' => $totalSales ? $totalSales->total_sale : 0,
                'new_sales_count' => $sale->new_sale,
                'refund_count' => $sale->refund_count
            ];
        });

        return view('total_sale', compact('salesByDate'));
    }

    public function projectWiseData($date)
    {
        // Retrieve sales data for the selected date
        $salesForDate = Sale::with('project')->whereDate('created_at', $date)->orderBy('sales_count', 'desc')->get();

        return view('perday_sale', compact('salesForDate', 'date'));
    }

    public function syncData()
    {
        $client = new Client();
        $response = $client->get(env('PROFILE_URL'));
        $html = $response->getBody()->getContents();

        $crawler = new Crawler($html);

// Fetching project names
        $projects = $crawler->filter('.product-list__heading')->each(function (Crawler $node) {
            return $node->text();
        });

// Fetching project IDs
        $projectIds = $crawler->filter('.product-list__heading a')->each(function (Crawler $node) {
            // Extract the project ID from the href attribute
            $href = $node->attr('href');
            // Assuming the project ID is always the last segment of the URL before the query string
            $projectId = basename(parse_url($href, PHP_URL_PATH));
            return $projectId;
        });

// Fetching price
        $price = $crawler->filter('.product-list__price')->each(function (Crawler $node) {
            // Extract the number using a regular expression that allows for currency symbols and spaces
            preg_match('/[0-9.,]+/', $node->text(), $matches);
            // Return the first match, which is the number
            return $matches[0];
        });
// Fetching sales counts
        $salesCount = $crawler->filter('.product-list__sales-desktop')->each(function (Crawler $node) {
            // Extract the number using a regular expression
            preg_match('/\d+/', $node->text(), $matches);
            // Return the first match, which is the number
            return $matches[0];
        });
// Fetching total sales counts
        $salesTotalCount = $crawler->filter('.user-info-header__stats-content')->each(function (Crawler $node) {
            return str_replace(',', '', $node->text());
        });


// Combine project names, project IDs, and sales counts
        $portfolioData = [];
        foreach ($projects as $index => $project) {
            $portfolioData[] = [
                'project_id' => $projectIds[$index],
                'project_name' => $project,
                'price' => $price[$index],
                'salesCount' => $salesCount[$index],
            ];
        }

//    return $portfolioData;

        foreach ($portfolioData as $portfolio) {


            $project = \App\Models\Project::query()->updateOrCreate(
                [
                    'uid' => $portfolio['project_id'],
                    'price' => $portfolio['price']
                ],
                [
                    'project_name' => $portfolio['project_name'],
                ]
            );
// Define the start and end of the current day
            $startOfDay = Carbon::now()->startOfDay();
            $endOfDay = Carbon::now()->endOfDay();

            $todayTotalSale = SaleHistory::query()->whereBetween('created_at', [$startOfDay, $endOfDay])->first();
            if ($todayTotalSale) {
                $todayTotalSale->update([
                    'total_sale' => $salesTotalCount[1],
                ]);
            } else {
                SaleHistory::create(
                    [
                        'total_sale' => $salesTotalCount[1]
                    ]
                );
            }


// Check if there's a sale record for today
            $todaySale = $project->sales()->whereBetween('created_at', [$startOfDay, $endOfDay])->first();
            $previousDaySale = $project->sales()
                ->whereDate('created_at', $startOfDay->subDay()) // Sales for the previous day
                ->first();

            if ($todaySale) {
                // If a sale record exists for today, update it
                $todaySale->update([
                    'sales_count' => $portfolio['salesCount'],
                    'new_sale_count' => $previousDaySale?->sales_count < $portfolio['salesCount'] ? $portfolio['salesCount'] - $previousDaySale?->sales_count : 0,
                    'refund_count' => $previousDaySale?->sales_count > $portfolio['salesCount'] ? $previousDaySale?->sales_count - $portfolio['salesCount'] : 0
                ]);
            } else {
                // If there's no sale record for today, create a new one
                $project->sales()->create([
                    'sales_count' => $portfolio['salesCount'],
                    'new_sale_count' => $previousDaySale?->sales_count < $portfolio['salesCount'] ? $portfolio['salesCount'] - $previousDaySale?->sales_count : 0,
                    'refund_count' => $previousDaySale?->sales_count > $portfolio['salesCount'] ? $previousDaySale?->sales_count - $portfolio['salesCount'] : 0
                ]);
            }
        }
        return redirect('/');
//        return \App\Models\SaleHistory::all();
    }

    public function latestSoldProduct($date)
    {
//        dd($date);
//        $yesterday = Carbon::yesterday();
        $yesterday = Carbon::parse($date)->subDay();

        $projects = Project::all();
        // Compare today's sales with yesterday's sales for each project
        $result = [];
        foreach ($projects as $project) {
            $todaySale = $project->sales()->whereDate('created_at', $date)->first();
            $yesterdaySale = $project->sales()
                ->whereDate('created_at', $yesterday)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($yesterdaySale && $todaySale && $todaySale->sales_count > $yesterdaySale->sales_count) {
                $difference = $todaySale->sales_count - $yesterdaySale->sales_count;
                $result[] = [
                    'project' => $project->project_name,
                    'price' => $project->price,
                    'quantity_sold' => $difference,
                ];
            }
        }


        return view('new_sale', compact('result', 'date'));

    }
    public function latestRefundProduct($date)
    {
//        dd($date);
//        $yesterday = Carbon::yesterday();
        $yesterday = Carbon::parse($date);

        $projects = Project::all();
        // Compare today's sales with yesterday's sales for each project
        $result = [];
        foreach ($projects as $project) {
            $refundProduct = $project->sales()->whereDate('created_at', $date)->where('refund_count', '>', 0)->first();
            if ($refundProduct){
                $result[] = [
                    'project' => $project->project_name,
                    'price' => $project->price,
                    'refund_count' => $refundProduct->refund_count,
                ];
            }
        }

        return view('refund', compact('result', 'date'));

    }
}
