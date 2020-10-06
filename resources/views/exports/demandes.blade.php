
<h1>Services Tous Azimuts</h1>
<table>
    <tbody>

        <tr>
            <td></td>
            <td>
                <p>Auto Parts - Car Rental</p>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <p>Phone Number: +2411560855 / +24177158215</p>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <p>Whatsapp Number: +24107158215</p>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <p>E-Mail: servicesazimuts@gmail.com</p>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <p>Address: Rue Fréderic Dioni, Case d'Écoute Port-Gentil</p>
            </td>
        </tr>
    </tbody>
</table>







<h1>Request for Quotation {{ $demande->nom }} {{$demande->id}}-10/2020</h1>
<table>
    <thead>
        <tr>
        </tr>
    </thead>
    <tbody>
            <tr>
            </tr>
            <tr></tr>
            <tr>
                <td>ID</td>
                <td>Product</td>

                <td>Quantity</td>
                <td>Offer</td>
                <td>Quantity Available</td>
            </tr>
            @foreach ($demande->sectionnables as $sectionnable)
                @if ($sectionnable->sectionnable_type === 'App\\Product')
                    <tr>
                        <td>{{ $sectionnable->pivot->id }}</td>
                        {{-- <td>{{ $sectionnable->product->name }}</td> --}}
                        @if ($sectionnable->pivot->traduction)
                            <td>{{ $sectionnable->pivot->traduction }}</td>
                        @else
                            <td >
                                {{ $sectionnable->product->handle->translation }}
                                @if ($sectionnable->product->handle->display1)
                                    <span>/ {{ $sectionnable->product[$sectionnable->product->handle->display1] }}</span>
                                @endif
                                @if ($sectionnable->product->handle->display2)
                                    <span>/ {{ $sectionnable->product[$sectionnable->product->handle->display2] }}</span>
                                @endif
                                @if ($sectionnable->product->handle->display3)
                                    <span>/ {{ $sectionnable->product[$sectionnable->product->handle->display3] }}</span>
                                @endif

                            </td>

                        @endif
                        <td>{{ $sectionnable->quantite}}</td>
                    </tr>
                @else
                    <tr>
                        <td>{{ $sectionnable->pivot->id }}</td>
                        {{-- <td>{{ $sectionnable->article->nom }}</td> --}}
                        <td>{{ $sectionnable->pivot->traduction }}</td>
                        <td>{{ $sectionnable->quantite}}</td>
                    </tr>
                @endif
            @endforeach

    </tbody>
</table>
