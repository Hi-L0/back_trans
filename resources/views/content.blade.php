<!DOCTYPE html>
<html>

<head>

    <style>
        body {
            margin-left: -30px;
        }

        .header {
            display: flex;
        }

        .date {
            margin-top: -10px;
            align-content: flex-end;
            text-align: right;
        }

        .distinataire {
            margin-right: 100px;
            float: right;
            /* text-align: right; */
            max-width: 200px;
            max-height: 200px;
        }

        .ville_Client {
            padding-top: 15px;
        }

        .info_F {
            display: flex;

            margin-top: 50px;

        }

        .title {

            font-weight: 700;
            font-size: 20px;


        }

        .codeF {
            padding-left: 50px;
            font-size: 14px;
        }

        table {
            margin-left: auto;
            width: 100%;

        }

        /* th {
            border: none;
            height: 40px;
            font-size: 12px;
            text-align: left;
        } */



        .t_header {
            background-color: #0c4a6e;
            /* border: 0px solid gray; */
            color: #FFFFFF;
            font-size: 14px;
            /*height: 40px;
            */
        }

        tbody>tr {
            text-align: right;

        }

        .descrip {
            margin-top: 30px;

        }

        .p_change {
            margin-top: 8px;
        }

        .mission_info {
            margin-top: 30px;
        }

        .matrc {
            font-weight: 600;
        }

        .autre_info {
            padding-top: 50px;
        }

        .mode_paiment {
            margin-top: 160px;
            /* float: left; */
        }

        .price_part {
            margin-top: -60px;
            margin-left: 60px;
            width: 230px;
            float: right;
            background-color: #0c4a6e;
            color: #FFFFFF;
        }

        .netPayLettre {
            text-align: right;
            margin-top: 100px;
            margin-left: 100px;

        }
    </style>
</head>

<body>
    <section class="header">
        <p class="date">Le
            {{-- {{ date('d-m-Y', strtotime($mission->facture->created_at)) }} --}}
            @if (!is_null($mission->facture->date))
                {{ date('d-m-Y', strtotime($mission->facture->date)) }}
            @endif
        </p>

        <div class="distinataire">

            {{ $mission->client->adresse }}
            <div class="ville_Client">
                {{ $mission->client->cp }} {{ $mission->client->ville }}
            </div>
            <div>
                {{ $mission->client->pays }}
            </div>
        </div>

    </section>

    <div class="info_F">
        <table style="margin-top:80px">
            <tr>
                <td class="title"> Facture </td>
                <td> {{ $mission->facture->code_facture }}</td>
                <td>Code Client: {{ $mission->client->code }} </td>
                <td>TVA Client: {{ $mission->client->tva }} </td>
            </tr>
        </table>
    </div>

    <section>
        <table>
            <thead class="t_header">
                <th style="text-align:left;">Designation</th>
                <th>Unité</th>
                <th>Quantite</th>
                <th>PU HT</th>
                <th>PU TTC</th>
                <th>Remise</th>
                <th>Total HT</th>
                <th>Total TTC</th>
                <th>Taxe</th>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: left; width:250px; vertical-align: top;">{{ $mission->facture->designation }}
                    </td>
                    <td style=" vertical-align: top;">{{ $mission->facture->unite }}</td>
                    <td style=" vertical-align: top;">{{ number_format($mission->facture->quantite, 2, ',', ' ') }}
                    </td>
                    <td width='80px' style=" vertical-align: top;">
                        {{ number_format($mission->facture->pu_ht, 2, ',', ' ') }}</td>
                    <td width='80px' style=" vertical-align: top;">
                        {{ number_format($mission->facture->pu_ttc, 2, ',', ' ') }}</td>
                    <td style=" vertical-align: top;">{{ $mission->facture->remise }}</td>
                    <td width='80px' style=" vertical-align: top;">
                        {{ number_format($mission->facture->total_ht, 2, ',', ' ') }}</td>
                    <td width='80px' style=" vertical-align: top;">
                        {{ number_format($mission->facture->total_ttc, 2, ',', ' ') }}</td>
                    <td style=" vertical-align: top;">{{ $mission->facture->taxe }}</td>

                </tr>
            </tbody>
        </table>
        <div class="descrip">
            Description :: <strong> {{ $mission->facture->description }} </strong>
        </div>
        <div class='p_change'>
            Prix: {{ $mission->facture->price_change }}€ Taux de change:{{ $mission->facture->taux_change }}dh
        </div>
    </section>

    <section class="mission_info">
        <div class="matrc">
            Matricule camion: {{ $mission->matricule }}
        </div>
        <div>
            Navire: <strong>{{ $mission->navire }}</strong>
        </div>
        <div>
            Embarqué le: @if (!is_null($mission->date_embarq))
                <strong>{{ date('d/m/Y', strtotime($mission->date_embarq)) }} </strong>
            @endif
        </div>
        <div>
            NOMBRE DE COLIS:<strong> {{ $mission->nb_colis }}</strong>
        </div>
        <div>
            NET Weight:<strong> {{ number_format($mission->poids, 2, ',', ' ') }}KG </strong>
        </div>
    </section>
    <section class="autre_info">
        <div>
            Delivery note: <strong>{{ $mission->facture->delivery_note }}</strong>
        </div>
        <div>
            PO Number: <strong>{{ $mission->facture->po_number }}</strong>
        </div>
        <div>
            invoice N°<b>{{ $mission->facture->invoiceNum }}</b>
        </div>
    </section>

    <section class="mode_paiment">
        <div>
            <div>
                Mode de règlement: {{ $mission->facture->mode_reglement }}
            </div>
            <div>
                {{ $mission->facture->commantaire }}
            </div>
            <div style="margin-top: 20px; font-size:14px;">
                Article d'exonération de TVA: 8-29 de la loi N°30-85 relative à la TVA
            </div>
        </div>
        <div class="price_part">
            <div style="font-size: 14px; padding-left:10px ">
                total HT: <span
                    style="text-align:right; margin-left:75px;">{{ number_format($mission->facture->total_ht, 2, ',', ' ') }}DHS</span>
            </div>
            <div style="padding-top: 10px; font-size:12px; text-align:right ">
                {{ $mission->facture->taxe }}. <span
                    style="margin-left:15px ">{{ number_format($mission->facture->total_ht, 2, ',', ' ') }}</span>
                <span
                    style="margin-left:40px; padding-right:5px;">{{ number_format($mission->facture->remise, 2, ',', ' ') }}DHS</span>
            </div>
            <div style="padding-top:50px; text-align:right;padding-right:5px  ">
                Total TTC: <span
                    style="padding-left:30px">{{ number_format($mission->facture->total_ttc, 2, ',', ' ') }}</span>
            </div>
            <div style="padding-top:20px; text-align:right;padding-right:5px ">
                Net à payer: <span
                    style="padding-left:30px">{{ number_format($mission->facture->total_ttc, 2, ',', ' ') }}</span>
            </div>
        </div>
    </section>
    <div class="netPayLettre">Net à payer: {{ $mission->facture->net_payer_letters }} DHS </div>

</body>



</html>
