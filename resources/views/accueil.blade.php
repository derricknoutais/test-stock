@extends('layouts.welcome')


@section('content')
    <div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-mt-16">
        <h1 class="tw-text-6xl tw-tracking-widest tw-font-extrabold">SHIPPER</h1>
        <div class="tw-flex tw-justify-center tw-w-2/6 d tw-mt-6 ">
            <img src="https://ordering.co/wp-content/uploads/2018/10/NHAutoparts-min-min.png" class="tw-w-full">
        </div>
        <div class="tw-mt-20">
            <a href="/template" class="tw-mx-10 tw-text-lg  tw-tracking-wider tw-shadow-outline tw-p-5 tw-border-0 tw-rounded-lg">TEMPLATES</a>
            <a href="/commande" class="tw-mx-10 tw-text-lg  tw-tracking-wider tw-shadow-outline tw-p-5 tw-border-0 tw-rounded-lg">COMMANDES</a>
            <a href="/produit" class="tw-mx-10 tw-text-lg  tw-tracking-wider tw-shadow-outline tw-p-5 tw-border-0 tw-rounded-lg">PRODUITS</a>
            <a href="/inventory-count" class="tw-mx-10 tw-text-lg  tw-tracking-wider tw-shadow-outline tw-p-5 tw-border-0 tw-rounded-lg">INVENTAIRES</a>
        </div>
    </div>
@endsection