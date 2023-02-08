<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Http\Request;

class RecordsController extends Controller
{
    public function __construct()
    {
        if (auth()->guard('api')->check()) {
            $this->middleware("auth:api");
        }
        if (auth()->guard('agent-api')->check()) {
            $this->middleware("auth:client");
        };
    }
    /**
     * viewing factures pdf
     * @param Models/Facture
     */
    public function view(Facture $facture)
    {
        $invoice = storage_path($facture->facture);
        if (auth()->guard('api')->id() == $facture->owner || auth()->guard('client')->id() == $facture->client) {
            if (file_exists($invoice)) {

                $headers = [
                    'Content-Type' => 'application/pdf'
                ];

                return response()->download($invoice, 'vewing invoice', $headers, 'inline');
            } else {
                abort(404, 'File not found!');
            }
        } else {
            return response()->json([
                'status' => 'danger',
                'message' => 'Unauthorized',
            ]);
        }
    }
}