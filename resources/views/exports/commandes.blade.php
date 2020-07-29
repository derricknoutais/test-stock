<h1>Services Tous Azimuts</h1>
<p>Auto Parts - Car Rental</p>
<p>Phone Number: +2411560855 / +24177158215</p>
<p>Whatsapp Number: +24107158215</p>
<p>E-Mail: servicesazimuts@gmail.com</p>
<p>Address: Rue Fréderic Dioni, Case d'Écoute Port-Gentil</p>

<h1>Bon Commande {{$bonCommande->nom}}</h1>
<table>
    <thead>
        <tr>
            <th>Name</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $bonCommande->nom }}</td>
        </tr>
        <tr></tr>
        <tr>
            <td>ID</td>
            <td>Product</td>
            <td>Quantity</td>
            <td>Unit Price</td>
            <td>Total</td>
        </tr>
        @foreach ($bonCommande->sectionnables as $sectionnable)
        @if ($sectionnable->sectionnable_type === 'App\\Product')
        <tr>
            <td>{{ $sectionnable->pivot->id }}</td>
            <td>{{ $sectionnable->product->name }}</td>
            <td>{{ $sectionnable->quantite}}</td>
            <td>{{ $sectionnable->pivot->prix_achat }}</td>
            <td>{{ $sectionnable->pivot->prix_achat * $sectionnable->quantite }}</td>
        </tr>
        @else
        <tr>
            <td>{{ $sectionnable->pivot->id }}</td>
            <td>{{ $sectionnable->article->nom }}</td>
            <td>{{ $sectionnable->quantite}}</td>
            <td>{{ $sectionnable->pivot->prix_achat }}</td>
            <td>{{ $sectionnable->pivot->prix_achat * $sectionnable->quantite }}</td>
        </tr>
        @endif
        @endforeach
        <tr></tr>
    </tbody>
</table>
