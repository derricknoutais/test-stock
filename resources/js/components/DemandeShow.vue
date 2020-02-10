<script>
export default {
    props : ['demande_prop'],
    data(){
        return {
            demande: null
        }
    },
    computed: {
        totalDemande(){
            var total = 0;
            this.demande.sectionnables.forEach(sectionnable => {
                total += (sectionnable.quantite * sectionnable.pivot.offre)
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
        }
    },
    created(){
        this.demande = this.demande_prop
    }
}
</script>