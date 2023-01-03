<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaController extends Controller
{
    //SELECT extract(year from date) as year, extract(month from date) as month, SUM(total_ttc) as chiffre_permonth from `factures` where owner = 1 and isClosed=true and extract(year from date)=extract(year from CURRENT_TIMESTAMP) GROUP by extract(month from date);

    public function getDataCA($an)
    {
        // $data = auth()->guard('api')->user()->closedFactures->sum('total_ttc')->where('YEAR(date)', $an)->groupBy('MONTH(date)');
        // $data=Facture::selectRow();
        if (auth()->guard('api')->check()) {
            //shows revenue (paid invoices only)
            $reveYearly = auth()->guard('api')->user()->paidFactures()->whereYear('date', '=', $an)->sum('total_ttc');
            $data = Facture::select(
                DB::raw('sum(total_ttc) as sums'),
                DB::raw("DATE_FORMAT(date,'%M %Y') as months"),
                DB::raw("DATE_FORMAT(date,'%Y') as year"),
                DB::raw("DATE_FORMAT(date,'%m') as monthKey")
            )->where('owner', auth()->guard('api')->id())
                ->where('isPaid', true)
                // ->where("DATE_FORMAT(date, '%Y')", $an) //i want to restrict data to the ($an) year
                ->whereYear('date', '=', $an)
                ->groupBy('months', 'year', 'monthKey')->get();

            //this is an array that sorts sums value into 12 months

            $dataPerMonth = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

            foreach ($data as $item) {
                $dataPerMonth[$item->monthKey - 1] = $item->sums;
            }
            //this is for the factures that hasn't been paid yet
            $data_revNotPaid = Facture::select(
                DB::raw('sum(total_ttc) as sums'),
                DB::raw('date'),
                DB::raw("DATE_FORMAT(date,'%M %Y') as months"),
                DB::raw("DATE_FORMAT(date,'%Y') as year"),
                DB::raw("delai_paiement as delai"),
                DB::raw("DATE_FORMAT(date,'%m') as monthKey")
            )->where('owner', auth()->guard('api')->id())
                ->where('isClosed', true)
                ->where('isPaid', false)
                // ->where("DATE_FORMAT(date, '%Y')", $an) //i want to restrict data to the ($an) year
                ->whereYear('date', '=', $an)
                ->groupBy('date', 'months', 'year', 'monthKey', 'delai')->get();

            //this is an array that sorts sums value into 12 months
            $revNotPaidPerMonth = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            //today's date
            $today = date('Y-m-d');
            $reveYearly_notpaid = 0;
            foreach ($data_revNotPaid as $item) {
                $paidelay = strftime('%Y-%m-%d', strtotime($item->date . $item->delai . 'days'));
                if ($today < $paidelay) { //i added this so that it checks if its in recovery state or it's just unpaid and still within the delay period
                    $reveYearly_notpaid = auth()->guard('api')->user()->closedFactures()->where('isPaid', false)->whereYear('date', '=', $an)->sum('total_ttc');
                    if ($revNotPaidPerMonth[$item->monthKey - 1] != 0) {
                        $revNotPaidPerMonth[$item->monthKey - 1] = $revNotPaidPerMonth[$item->monthKey - 1] + $item->sums;
                    } else {
                        $revNotPaidPerMonth[$item->monthKey - 1] = $item->sums;
                    }
                }
            }
            // [0,10000,5000,7000,9000,0,0,0,0,15000,0,0]
            //for recouvrement stats we added a new attribut to group by it's 'date'
            $recouvrementPerMonth = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $j = 0;
            $sumRecouvrement = 0;
            foreach ($data_revNotPaid as $item) {
                $paidelay = strftime('%Y-%m-%d', strtotime($item->date . $item->delai . 'days')); //delai paiment date
                if ($today > $paidelay) {
                    $i = $j;
                    $facturesList[$i] = $item;
                    $j = $i + 1;
                    $sumRecouvrement = $sumRecouvrement + $item->sums;
                    $recouvrementPerMonth[$item->monthKey - 1] = $recouvrementPerMonth[$item->monthKey - 1] + $item->sums;
                }
            }
            return response()->json([
                'revenue' => $reveYearly,
                'dataPerMonth' => $dataPerMonth,
                'revNotPaid' => $reveYearly_notpaid,
                'revNotPaidPerMonth' => $revNotPaidPerMonth,
                'recouvrement' => $sumRecouvrement,
                'recouvrementPerMonth' => $recouvrementPerMonth,
                'data' => $data_revNotPaid
            ]);
            // return response()->json([
            //     'data' => $data,
            // ]);
        }
        return response()->json([
            'status' => 'danger',
            'message' => 'unauthenticated',
        ]);
    }

    //recouvrement stats (permonths and the sum of the recouvrement)

    // public function getRecouvrementRev($an)
    // {
    //     if (auth()->guard('api')->check()) {
    //         $reveYearly_notpaid = auth()->guard('api')->user()->closedFactures()->where('isPaid', false)->whereYear('date', '=', $an)->sum('total_ttc');
    //         $data_revNotPaid = Facture::select(
    //             DB::raw('sum(total_ttc) as sums'),
    //             DB::raw('date as date'),
    //             DB::raw("DATE_FORMAT(date,'%M %Y') as months"),
    //             DB::raw("DATE_FORMAT(date,'%Y') as year"),
    //             DB::raw("delai_paiement as delai"),
    //             DB::raw("DATE_FORMAT(date,'%m') as monthKey")
    //         )->where('owner', auth()->guard('api')->id())
    //             ->where('isClosed', true)
    //             ->where('isPaid', false)
    //             // ->where("DATE_FORMAT(date, '%Y')", $an) //i want to restrict data to the ($an) year
    //             ->whereYear('date', '=', $an)
    //             ->groupBy('date', 'months', 'year', 'monthKey', 'delai')->get();

    //         //this is an array that sorts sums value into 12 months

    //         $recouvrementPerMonth = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    //         $j = 0;
    //         $sum = 0;
    //         foreach ($data_revNotPaid as $item) {
    //             $today = date('Y-m-d');
    //             $paidelay = strftime('%Y-%m-%d', strtotime($item->date . $item->delai . 'days')); //delai paiment date
    //             if ($today > $paidelay) {
    //                 $i = $j;
    //                 $facturesList[$i] = $item;
    //                 $j = $i + 1;
    //                 $sum = $sum + $item->sums;
    //                 $recouvrementPerMonth[$item->monthKey - 1] = $recouvrementPerMonth[$item->monthKey - 1] + $item->sums;
    //             }
    //         }
    //         // [0,10000,5000,7000,9000,0,0,0,0,15000,0,0]
    //         return response()->json([
    //             'recouvrement' => $sum,
    //             'recouvrementPerMonth' => $recouvrementPerMonth,
    //         ]);
    //         // return response()->json([
    //         //     'data' => $data,
    //         // ]);
    //     }
    // }

    // //test operations on date attributs
    // public function getYearOp() //example
    // {
    //     $today = date('M Y');
    //     $date = '2022-12-07';
    //     $d = 30; //paiment delai stated by the invoice maker
    //     $paidelai = strftime('%M %Y', strtotime($date . $d . 'days')); //delai paiment date
    //     if ($today > $paidelai) {
    //         return response()->json([
    //             'message' => 'recouvrement etat',
    //             'delaipaimentdate' => $paidelai,
    //             $today
    //         ]);
    //     } else {
    //         return response()->json([
    //             'message' => 'not recouvrement etat',
    //             'delaipaimentdate' => $paidelai,
    //         ]);
    //     }
    // }
}