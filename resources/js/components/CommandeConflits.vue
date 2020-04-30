
<script>
export default {
    props: ['commande_prop'],
    data(){
        return {
            commande: null,

        }
    },
    methods:{
        selectionnerElementConflictuel(element){
            axios.post('/commande/' + this.commande.id + '/résoudre-conflit', element).then(response => {
                console.log(response.data);

            }).catch(error => {
                console.log(error);
            });
        }
    },
    created(){
        this.commande = this.commande_prop
        // Pour chaque Conflit
        this.commande.conflits.forEach( conflit => {
            // Map tous les éléments conflictuels
            conflit.elements_conflictuels.map( (element) => {
                var found = this.commande.demandes.find( demande => {
                    return element.demande_id === demande.id
                } )
                element.demande = found
            } )
        });
    }
}
</script>
