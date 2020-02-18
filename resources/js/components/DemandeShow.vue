<script>
export default {
    props : ['demande_prop'],
    data(){
        return {
            demande: null,
            cardNumber: null, 
            options: {
              creditCard: true,
              delimiter: '-',
            }
        }
    },
    computed: {
        totalDemande(){
            var total = 0;
            this.demande.sectionnables.forEach(sectionnable => {
                total += (sectionnable.pivot.quantite_offerte * sectionnable.pivot.offre)
            });
            return total;
        }
    },
    methods:{
        enregisterOffre(sectionnable){
            axios.put('/demande/' + this.demande.id + '/update-product', sectionnable).then(response => {
                console.log(response.data);
                
            }).catch(error => {
                console.log(error);
            });
        },
    },
    created(){
        this.demande = this.demande_prop
    }
}
</script>