<h1>Services Tous Azimuts</h1>
<p>Vente de Pièces Détachées - Location de Véhicule</p>
<p>Nº Téléphone: +2411560855 / +24177158215</p>
<p>E-Mail: servicesazimuts@gmail.com</p>
<p>B.P: 1268 / Adresse: Rue Fréderic Dioni, Case d'Écoute Port-Gentil</p>

<h1>Demande {{$demande->nom}}</h1>
<table>
    <thead>
        <tr>
            <th>Name</th>
        </tr>
    </thead>
    <tbody>
            <tr>
                <td>{{ $demande->nom }}</td>
            </tr>
            <tr></tr>
            <tr>
                <td>Identifiant</td>
                <td>Produit</td>
                <td>Quantite</td>
                <td>Offre</td>
            </tr>
            @foreach ($demande->sectionnables as $sectionnable)
                <tr>
                    <td>{{ $sectionnable->pivot->id }}</td>
                    <td>{{ $sectionnable->product->name }}</td>
                    <td>{{ $sectionnable->quantite}}</td>
                </tr>
            @endforeach
            <tr></tr>
    </tbody>
</table>
