@extends('layouts.welcome')


@section('content')
    <facture :data="{{ $factures }}" inline-template>
        <div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Montant Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="facture in data">
                        <td scope="row">
                            <a :href="'/facture/' + facture.id ">
                                @{{ facture.nom }}
                            </a>
                        </td>
                        <td>@{{ total(facture) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td scope="row"></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </facture>
@endsection
