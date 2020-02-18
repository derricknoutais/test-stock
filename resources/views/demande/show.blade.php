@extends('layouts.welcome')


@section('content')

<demande-show :demande_prop="{{$demande}}" inline-template>
    <div class="tw-container tw-mx-auto">

        <h1 class="tw-text-5xl tw-text-center tw-mt-20 tw-uppercase">Demande @{{ demande.nom }}</h1>

        <table class="table tw-mt-5">
            <thead>
                <tr>
                    <th></th>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Quantité Offerte</th>
                    <th>Offre</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="sectionnable in demande.sectionnables" v-if="sectionnable.product">
                    <td >
                        
                    </td>
                    <td scope="row">@{{ sectionnable.product.name }}</td>
                    <td>@{{ sectionnable.quantite }} </td>
                    <td class="tw-flex tw-items-center">
                        <i class="fas fa-arrow-down  tw-text-red-500 tw-px-5" v-if="sectionnable.pivot.quantite_offerte < sectionnable.quantite"></i>
                        <i class="fas fa-arrow-up  tw-text-yellow-600 tw-px-5" v-if="sectionnable.pivot.quantite_offerte > sectionnable.quantite"></i>
                        <i class="fas fa-thumbs-up  tw-text-green-500 tw-px-5" v-if="sectionnable.pivot.quantite_offerte === sectionnable.quantite"></i>

                        <input type="text" class="form-control" v-model.number="sectionnable.pivot.quantite_offerte" @input="enregisterOffre(sectionnable)">
                    </td>
                    <td>
                        <input type="text" class="form-control" v-model.number="sectionnable.pivot.offre" @input="enregisterOffre(sectionnable)">
                    </td>
                    <td>
                        @{{ sectionnable.pivot.quantite_offerte * sectionnable.pivot.offre | currency }}
                    </td>
                </tr>
                <tr v-for="sectionnable in demande.sectionnables" v-if="sectionnable.article">
                    <td scope="row">@{{ sectionnable.article.nom }}</td>
                    <td>@{{ sectionnable.quantite }} </td>
                    <td>
                        
                        <input type="text" class="form-control" v-model.number="sectionnable.pivot.offre" @input="enregisterOffre(sectionnable)">
                    </td>
                    <td>
                        @{{ sectionnable.quantite * sectionnable.pivot.offre | currency}}
                    </td>
                </tr>
                <tr>
                    <td colspan="5" class="tw-text-right">Total</td>
                    <td>@{{totalDemande | currency }}</td>
                </tr>
            </tbody>
        </table>

    </div>


</demande-show>


@endsection