
<script>
export default {
    props: ['commandes_prop'],
    data(){
        return {
            commandes: null
        }
    },
    methods: {
        total(commande){
            var total = 0;
            if(commande.bons_commandes){
                commande.bons_commandes.forEach( bon => {
                    if(bon.sectionnables){
                        bon.sectionnables.forEach(sectionnable => {
                            total += (sectionnable.pivot.prix_achat * sectionnable.pivot.quantite)
                        });
                    }
                });
            }
            return total;
        },
        nombreProduits(commande){
            var total = 0;
            if(commande.bons_commandes){
                commande.bons_commandes.forEach( bon => {
                    if(bon.sectionnables){
                        total += bon.sectionnables.length
                    }
                });
            }
            return total;
        },
    },
    created(){
        this.commandes = this.commandes_prop
    }
}
</script>