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
            $reveYearly_notpaid = auth()->guard('api')->user()->closedFactures()->where('isPaid', false)->whereYear('date', '=', $an)->sum('total_ttc');
            $data_revNotPaid = Facture::select(
                DB::raw('sum(total_ttc) as sums'),
                DB::raw("DATE_FORMAT(date,'%M %Y') as months"),
                DB::raw("DATE_FORMAT(date,'%Y') as year"),
                DB::raw("DATE_FORMAT(date,'%m') as monthKey")
            )->where('owner', auth()->guard('api')->id())
                ->where('isClosed', true)
                ->where('isPaid', false)
                // ->where("DATE_FORMAT(date, '%Y')", $an) //i want to restrict data to the ($an) year
                ->whereYear('date', '=', $an)
                ->groupBy('months', 'year', 'monthKey')->get();

            //this is an array that sorts sums value into 12 months

            $revNotPaidPerMonth = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

            foreach ($data_revNotPaid as $item) {
                $revNotPaidPerMonth[$item->monthKey - 1] = $item->sums;
            }
            // [0,10000,5000,7000,9000,0,0,0,0,15000,0,0]
            return response()->json([
                'revenue' => $reveYearly,
                'dataPerMonth' => $dataPerMonth,
                'revNotPaid' => $reveYearly_notpaid,
                'revNotPaidPerMonth' => $revNotPaidPerMonth,
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
}