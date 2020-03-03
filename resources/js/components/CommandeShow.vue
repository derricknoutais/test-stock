<script>
export default {
    props: ['commande_prop', 'products_prop', 'templates_prop' ],
    data(){
        return {

            show_products: false,
            selected_product: false,
            selected_template: false,
            selected_article: false,
            new_section: 'Huiles Moteur',
            isUpdating: false,
            isDeleting: false,
            commande: null,
            isLoading : {
                stock: false,
                reorder_point: false,
            },
            reorderPoint : null,

            products : null,
            templates: null,
            articles: false,
            section_being_updated: false,
            section_being_deleted: false,
            editing: false,
            
            article: false,
            sectionnable_type: false, 
            list: false,
            label: '',
        }
    },
    watch: {
        'selected_article' : function(){

                document.getElementById('quantiteInput').focus() 

            
        }
    },
    methods:{
        addProduct(){
            console.log(this.selected_product)
            axios.post('/product-commande', {commande_id : this.commande.id, product_id : this.selected_product.id } ).then(response => {
                if(response.data === 'OK'){
                    this.commande.products.push(this.selected_product)
                }
            }).catch(error => {
                console.log(error)
                // alert('Un problème est survenu lors du chargement des stocks. Veuillez relancer la MàJ des Stocks')
            });
        },
        addTemplate(){
            axios.post('/template-commande', {commande_id : this.commande.id, template_id : this.selected_template.id } ).then(response => {
                if(response.data === 'OK'){
                    this.commande.templates.push(this.selected_template)
                    this.$forceUpdate()
                }
            }).catch(error => {
                console.log(error);
            });
        },
        addSection(){
            if(this.isUpdating === true){
                this.updateSection(this.section_being_updated)
                this.isUpdating = false
                return 0;
            }
            if(this.new_section){
                axios.post('/section', {commande: this.commande_prop.id, section: this.new_section} ).then(response => {
                    this.commande.sections.push({
                        id: response.data.id,
                        nom: this.new_section
                    });
                    $('#section').modal('hide')
                    window.location.reload()
                }).catch(error => {
                    console.log(error);
                });
            }
        },
        addProductToSection(section){
            this.new_section = section
            
            axios.post('/product-section',{ section: section, product: this.selected_article, type: 'App\\' + this.sectionnable_type} ).then(response => {
                console.log(response.data)
                var found = this.commande.sections.find( (sect, section) => {
                    return sect.id ===  this.new_section
                })  
                if(this.sectionnable_type === 'Article'){
                    found.articles.unshift({
                        nom : this.selected_article.nom,
                        pivot: {
                            id: response.data.id,
                            quantite : this.selected_article.quantite
                        },
                    });

                    
                } else if(this.sectionnable_type === 'Template'){
                    response.data.forEach(element => {
                        found.products.unshift({
                            name: element.name
                        })
                    });
                } else {
                    found.products.unshift({
                        name:this.selected_article.name,
                        pivot: {
                            id: response.data.id,
                            quantite : this.selected_article.quantite
                        },
                    })

                }
                
                this.new_section = false   
                document.getElementById('select').focus()  
                document.getElementById('quantiteInput').value = 0

            }).catch(error => {
                console.log(error);
            });
        },
        majStock(){
            
            if(this.numberOfProducts > 0){
                // Turn Stock isLoading Flag On 
                this.isLoading.stock = true;
                // Grab stock from vend
                axios.get('/api/stock').then( response => {
                    if (this.commande.products) {
                        // If I get response Iterate over Products
                        this.commande.products.forEach( product => {
                            // Foreach Product Iterate over Stock
                            response.data.forEach( stock => {
                                // If Product Matches Stock ... 
                                if(product.product_id === stock.product_id)
                                {
                                    // Add Stock to Product
                                    product.stock = stock.inventory_level
                                }
                            });
                        });  
                    }
                    
                    if(this.commande.templates){
                        // Iterate over Templates
                        this.commande.templates.forEach( template => {
                            // Foreach Template Iterate over products
                            template.products.forEach( product => {
                                // Foreach Product Iterate over Stock
                                response.data.forEach( stock => {
                                    // If Product Matches Stock ... 
                                    if(product.product_id === stock.product_id)
                                    {
                                        // Add Stock to Product
                                        product.stock = stock.inventory_level
                                    }
                                });
                            }); 
                        });
                    }

                    if(this.commande.reorderpoint[0]){
                        // For Reorder Point Iterate over products
                        this.commande.reorderpoint[0].products.forEach( product => {
                            // Foreach Product Iterate over Stock
                            response.data.forEach( stock => {
                                // If Product Matches Stock ... 
                                if(product.product_id === stock.product_id)
                                {
                                    // Add Stock to Product
                                    product.stock = stock.inventory_level
                                }
                            });
                        });
                    }

                    this.$forceUpdate();
                    this.isLoading.stock = false;
                }).catch(error => {
                    console.log(error);
                });
            } else {
                alert('Aucun Produit dans la commande. Ajoutez des produits')
            }
            
        },
        addReorderpoint(){
            axios.post('/reorderpoint-commande', {commande_id : this.commande.id}).then(response => {
                console.log(response.data);
            }).catch(error => {
                console.log(error);
            });
        },
        // Toggle Editing 
        toggleEdit(){
            this.editing = ! this.editing 
        },
        // Enregistre les quantités souhaitées
        save(){
            // this.commande.templates.forEach( template => {
            //     template.products.map( template_product => {
            //         var found = this.commande.products.find( product => {
            //             if(product.id === template_product.id){
            //                 found.pivot.quantity = template_product.quantity
            //             }
            //         })
                    
            //     })
            // })
            

            axios.post('/commande-quantité', this.commande ).then(response => {
                console.log(response.data);
            }).catch(error => {
                console.log(error);
            });
        },
        mapArrays(){
            if(this.commande && this.commande.templates[0] && this.commande.templates[0].products){
                this.commande.templates[0].products.map( template_product => {
                    var found = this.commande.products.find( product => {
                        return product.id === template_product.id
                    })
                    template_product.quantity = found.pivot.quantity
                })
            }
        },
        deleteProductSection(section, article, type){
        
            axios.get('/section-product/delete/' + article.id + '/' + section.id ).then(response => {
                console.log(response.data);
                if(response.data === 0){
                    alert('Article Pas Supprimé. Veuillez Reesayé')
                } else {
                    var section_trouvée = this.commande.sections.find(sect => {
                        return sect.id === section.id
                    })
                    if(type === 'Article'){
                        var article_trouvée = section_trouvée.articles.find( art => {
                            return art.id === article.id
                        })
                        var index = section_trouvée.articles.indexOf(article_trouvée)
                        section_trouvée.articles.splice(index, 1)
                        this.$forceUpdate()

                    } else if (type === 'Product'){
                        var article_trouvée = section_trouvée.products.find( prod => {
                            return prod.id === article.id
                        })

                        var index = section_trouvée.products.indexOf(article_trouvée)
                        section_trouvée.products.splice(index, 1)
                        this.$forceUpdate()
                    }
                    
                    
                    // alert('Article Suprrimé')
                }
            }).catch(error => {
                console.log(error);
            });
        },
        saveQuantity(section, article){
            console.log(article)
            axios.put('/article-update',  {section : section, article: article}).then(response => {
                console.log(response.data);
                
            }).catch(error => {
                console.log(error);
            });
        },
        openEditModal(section){
            this.isUpdating = true
            this.section_being_updated = section
            $('#section').modal('show')
            this.new_section = section.nom

        },
        openDeleteModal(section){
            this.isDeleting = true
            this.section_being_deleted = section
            $('#sectionDelete').modal('show')
        },
        updateSection(section){
            
            
            axios.put('/section/' + this.section_being_updated.id, {nom:this.new_section}).then(response => {
                console.log(response.data);
                this.section_being_updated.nom = this.new_section
                this.isUpdating = false
                this.section_being_updated = false
                this.new_section = false
                this.$forceUpdate()
                $('#section').modal('hide')
            }).catch(error => {
                console.log(error);
            });
        },
        removeSection(section){
            axios.delete('/section/' + this.section_being_deleted.id).then(response => {
                
                var index = this.commande.sections.indexOf(section)
                this.commande.sections.splice(index, 1)
                $('#sectionDelete').modal('hide')
                this.$forceUpdate()
                
                console.log(response.data);
            }).catch(error => {
                console.log(error);
            });
        },
        removeProduct(section, produit, type){
            
            axios.delete('/sectionnable/' + produit.pivot.id).then(response => {
                
                if(type === 'Product'){
                    var index = section.products.indexOf(produit)
                    section.products.splice(index, 1)
                } else {
                    var index = section.articles.indexOf(produit)
                    section.articles.splice(index, 1)
                }
                this.$forceUpdate()
            }).catch(error => {
                console.log(error);
            });
        }

    },
    computed : {
        numberOfProducts(){
            var total = 0;
            if(this.commande){

                if(this.commande.templates){
                    this.commande.templates.forEach( template => {
                        if(template.products){
                            total += template.products.length;
                        } else {
                            total += 0;
                        }
                    });
                }

                if(this.commande.reorderpoint){
                    this.commande.reorderpoint.forEach( reorderpoint => {
                        total += reorderpoint.products.length;
                    });
                }

                if(this.commande.products){
                    total += this.commande.products.length;
                }
                if(this.commande.sections ){
                    this.commande.sections.forEach( section => {
                        if( section.articles.length > 0 || section.products.length > 0 ){
                            total += section.articles.length + section.products.length
                        }
                    })
                }

            }
            return total;
        }, 
        numberOfNewProducts(){
            var total = 0;
            if(this.commande.sections ){
                this.commande.sections.forEach( section => {
                    if( section.articles.length > 0  ){
                        total += section.articles.length 
                    }
                })
            }
            return total
        },
        numberOfVendProducts(){
            var total = 0;
            if(this.commande.sections ){
                this.commande.sections.forEach( section => {
                    if( section.products.length > 0  ){
                        total += section.products.length 
                    }
                })
            }
            return total  
        },
        prixMoyenDemande(){
            var total = 0;
            if(this.commande.demandes.length > 1){
                total = this.commande.demandes.reduce( (a,b) => {

                    if(a.sectionnables && a.sectionnables.length > 0){
                        a.total = a.sectionnables.reduce( (x,y) => {
                            // return (x.pivot.quantite_offerte * x.pivot.offre) + (y.pivot.quantite_offerte * y.pivot.offre)
                        })
                    }
                    
                    if(b.sectionnables && b.sectionnables.length > 0){
                        b.total = b.sectionnables.reduce( (x,y) => {
                            // return (x.pivot.quantite_offerte * x.pivot.offre) + (y.pivot.quantite_offerte * y.pivot.o ffre)
                        })
                    }
                    
                    return ( a.total + b.total )
                })
                
            } else if(this.commande.demandes.length === 1 && this.commande.demandes[0].sectionnables.length > 0){
                total = this.commande.demandes[0].sectionnables.reduce( (x,y) => {
                    // return (x.pivot.quantite_offerte * x.pivot.offre) + (y.pivot.quantite_offerte * y.pivot.offre)
                })
            } else {
                return '*********'
            }
            var prix_moyen = 0
            return prix_moyen = total / (this.commande.demandes.length)
        },
        totalBonsCommandes(){

        },
        list_type(){
            if(this.sectionnable_type === 'Product'){
                this.label = 'name'
                return this.products
            } else if(this.sectionnable_type === 'Article'){
                this.label = 'nom'
                return this.articles
            } else if(this.sectionnable_type === 'Template'){
                this.label = 'name'
                return this.templates
            } else {
                return this.products
            }
        }
    },
    created(){
        if (this.commande_prop) {
            this.commande = this.commande_prop
        }
        if(this.products_prop){
            this.products = this.products_prop
        }

        if(this.templates_prop){
            this.templates = this.templates_prop
        }

        axios.get('http://azimuts.ga/article/api/non-commandé').then(response => {
            this.articles = response.data
            this.articles.map( article => {
                if(article.fiche_renseignement){

                    if(article.fiche_renseignement.marque){
                        article.marque = article.fiche_renseignement.marque.nom
                        article.search_name = article.nom + ' ' + article.fiche_renseignement.marque.nom
                    }

                    if(article.fiche_renseignement.type){
                        article.type = article.fiche_renseignement.type.nom
                        article.search_name += ' ' + article.fiche_renseignement.type.nom
                    }
                    if(article.fiche_renseignement.moteur){
                        article.moteur = article.fiche_renseignement.moteur.nom
                        article.search_name += ' ' + article.fiche_renseignement.moteur.nom
                    }
                }
            })


        }).catch(error => {
            console.log(error);
        });

        
        
        this.mapArrays()

        
    }
}
</script>