<script>
export default {
    props: ['commande_prop', 'fournisseurs_prop'],
    data(){
        return {
            isLoading : {
                toutesDemandes: false
            },
            fournisseurs: null,
            nouvelle_demande: null,
            commande: null,
            selected_products: [],
            selected_fournisseur: null,
            selected_demandes: [],
            filtered: {
                sections: []
            }
        }
    },
    computed : {

    },
    methods:{
        niveauDAchevement(section, result){
            if(section.products)
                var total = section.products.length

            var complété = 0;
            section.products.forEach( product => {
                if(product.demandes.length > 0){
                    complété ++
                }
            })
            var niveau = complété + '/' + total
            var pourcentage = Math.ceil((complété / total) * 100)
            if(result === 'niveau'){
                return niveau
            } else if( result === 'pourcentage'){
                return pourcentage
            }
        },
        toggleSection(section){
            section.show = !section.show
            this.$forceUpdate()
        },
        filter_demandé(){
            this.filtered.sections = []
            // Pour chaque section
            this.commande.sections.forEach( sect => {

                var section = sect

                // On backup les produits de la section
                var products_to_search = sect.products
                // On reset les produits dans la section filtered
                var result = []

                sect.products.forEach( product => {
                    if(product.demandes.length > 0){
                        result.push(product)
                    }
                });
                console.log(result)
                section.products = result
                // On pousse la section dans filtered
                this.filtered.sections.push(section)


            });
            this.$forceUpdate()
        },
        filter_non_demandé(){
            this.filtered.sections = []
            // Pour chaque section
            this.commande.sections.forEach( sect => {

                var section = sect

                // On backup les produits de la section
                var products_to_search = sect.products
                // On reset les produits dans la section filtered
                var result = []

                sect.products.forEach( product => {
                    if(product.demandes.length === 0){
                        result.push(product)
                    }
                });
                console.log(result)
                section.products = result
                // On pousse la section dans filtered
                this.filtered.sections.push(section)


            });
            this.$forceUpdate()
        },
        réinitialiser(){
            location.reload()
        },
        saveDemande(){
            axios.post('/demande', { demande: this.nouvelle_demande , commande: this.commande.id, fournisseur: this.selected_fournisseur}).then(response => {
                console.log(response.data);
                this.commande.demandes.push({
                    nom: this.selected_fournisseur.nom,
                    fournisseur_id: this.selected_fournisseur.id,
                    id: response.data.id,
                    sectionnables: []
                })
                $('#demande-modal').modal('hide')

            }).catch(error => {
                console.log(error);
            });
        },
        checkAll(section){
            console.log('hello');
            if(section.checkAll === false ) {
                // alert('select them')
                section.products.forEach( product => {
                    if( ! this.selected_products.includes(product))
                        this.selected_products.push(product)
                })
                section.articles.forEach( article => {
                    if( ! this.selected_products.includes(article) )
                        this.selected_products.push(article)
                })
            } else {
                // alert('unselect')
                section.products.forEach( product => {
                    this.selected_products.forEach((prod, index) => {
                        if(prod === product){
                            this.selected_products.splice(index, 1)
                        }
                    });
                });
                section.articles.forEach( article => {
                    this.selected_products.forEach((prod, index) => {
                        if(prod === article){
                            this.selected_products.splice(index, 1)
                        }
                    });
                });
                section.checkAll = false
            }

        },
        addProductsToDemandes(){

                // Pour chaque demande selectionnée
                this.selected_demandes.forEach( sel_dem => {
                    // Pour chaque demande de cette commande
                    this.commande.demandes.forEach(demande => {
                        // Si la demande selectionnée correspond a une demande en cours
                        if(sel_dem.id === demande.id){
                            // Pour chaque produit selectionnée
                            this.selected_products.forEach( sel_prod => {
                                // Initialise la variable found = false
                                var found = false
                                // Pour chaque sectionnable (Articles & Produits) ...
                                for (let index = 0; index < demande.sectionnables.length; index++) {
                                    // Si un des sectionnables correspond à un des produits selectionnés
                                    if(sel_prod.id == demande.sectionnables[index].sectionnable_id){
                                        // Donc ca existe déja dans la base de données
                                        found = true;
                                        break;
                                    }
                                }

                                if(! found){
                                    axios.post('/demande-sectionnable', { products: sel_prod, demandes: sel_dem}).then(response => {
                                        // console.log(response.data);
                                        // location.reload()
                                        // Insère Chaque produit selectionné dans la demande qui correspond
                                        this.commande.demandes.forEach( demande => {

                                            this.selected_demandes.forEach( dem => {

                                                if( dem.id === demande.id ){

                                                    this.selected_products.forEach( prod => {
                                                        var found = false
                                                        demande.sectionnables.forEach( sectionnable => {
                                                            if(prod.id === sectionnable.sectionnable_id){
                                                                found = true
                                                            }
                                                        });
                                                        console.log( prod.name + found )
                                                        if( ! found){
                                                            demande.sectionnables.push({
                                                                demande_id: dem.id,
                                                                sectionnable_id : prod.id
                                                            });
                                                        } else {

                                                        }


                                                    });

                                                }
                                            })
                                        })
                                        $('#ajouter-demande-modal').modal('hide')
                                        this.$forceUpdate()
                                    }).catch( error => {
                                        location.reload();
                                        alert('Tous les produits nont pas été entrés. Les duplicatas ont été supprimés automatiquement')
                                    });
                                } else {
                                    this.$swal({
                                        text: 'Les produits/articles sélectionnés font déjà partie de ces demandes'
                                    })
                                }
                            })


                        }
                    })
                });


        },
        dispatchProduits(){
            this.isLoading.toutesDemandes = true
            axios.get('/commande/'+ this.commande.id + '/dispatch-produits-dans-demandes').then(response => {
                this.isLoading.toutesDemandes = false
                window.location.reload()

            }).catch(error => {
                console.log(error);
            });
        }

    },
    created(){
        this.commande = this.commande_prop
        this.commande.sections.forEach( section => {
            section.checkAll = false
            section.show = false
        });
        this.commande.sections.forEach( section => {
            section.products.map( prod => {
                prod.show = false
                var found = section.sectionnables.find( sectionnable => {
                    if(sectionnable.sectionnable_type === "App\\Product" && sectionnable.sectionnable_id === prod.id){
                        return sectionnable;
                    }
                })
                prod.demandes = found.demandes
            })
            section.articles.map( prod => {
                prod.show = false
                var found = section.sectionnables.find( sectionnable => {
                    if( sectionnable.sectionnable_type === "App\\Article" )
                    {

                    }
                    if( sectionnable.sectionnable_type === "App\\Article" && sectionnable.sectionnable_id == prod.pivot.sectionnable_id)
                    {
                        return sectionnable;
                    }
                })
                prod.demandes = found.demandes
            })

        })
        this.fournisseurs = this.fournisseurs_prop
    }
}
</script>
