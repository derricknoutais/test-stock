
<script>
export default {
    props : ['commande_prop'],
    data(){
        return {
            commande: null
        }
    },
    computed: {
        
    },
    methods:{
        totalDemande(demande){
            var total = 0
            demande.sectionnables.forEach(sectionnable => {
                total += (sectionnable.pivot.quantite_offerte * sectionnable.pivot.offre)
            });
            return total
        },
        générerBons(){  
            // console.log('Hello')
            axios.get('/commande/' + this.commande.id + '/générer-bons').then(response => {
                console.log(response.data);
                
            }).catch(error => {
                console.log(error);
            });
        }
    },
    created(){
        this.commande = this.commande_prop
    }
}
</script>