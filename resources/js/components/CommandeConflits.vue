
<script>
export default {
    props: ['commande_prop'],
    data(){
        return {
            commande: null,

        }
    },
    methods:{
        selectionnerElementConflictuel(element, elements_conflictuels){
            console.log(element)
            console.log(elements_conflictuels)
            axios.post('/commande/' + this.commande.id + '/résoudre-conflit',
                {element : element, elements_conflictuels : elements_conflictuels}
            ).then(response => {

            }).catch(error => {
                console.log(error);
            });
        }
    },
    created(){
        //
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
