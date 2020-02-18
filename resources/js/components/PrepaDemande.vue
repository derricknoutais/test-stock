<script>
export default {
    props: ['commande_prop'],
    data(){
        return {
            nouvelle_demande: null,
            commande: null,
            selected_products: [],
            selected_demandes: []
        }
    },
    methods:{
        saveDemande(){
            axios.post('/demande', { demande: this.nouvelle_demande , commande: this.commande.id, fournisseur: null}).then(response => {
                console.log(response.data);
                this.commande.demandes.push({
                    nom: this.nouvelle_demande
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
            axios.post('/demande-sectionnable', { products: this.selected_products, demandes: this.selected_demandes}).then(response => {
                console.log(response.data);
                location.reload()
                this.commande.demandes.forEach( demande => {
                    this.selected_demandes.forEach( dem => {
                        if( dem.id === demande.id ){
                            demande.sectionnables.push({
                                demande_id: 1,
                                sectionnable_id : 1
                            })
                        }
                    })
                })
                this.$forceUpdate()
            }).catch( error => {
                console.log(error);
            });
        }
    },
    created(){
        this.commande = this.commande_prop  
        this.commande.sections.forEach( section => {
            section.checkAll = false
        })
    }
}
</script>