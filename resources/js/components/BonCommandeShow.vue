
<script>

export default {
    props: [ 'bc_prop' ],

    data(){
        return {
            bc: null,
            editMode : false
        }
    },
    computed:{
        montantTotal(){
            var total = 0
            this.bc.sectionnables.forEach( sect => {
                total += (sect.pivot.quantite * sect.pivot.prix_achat)
            })
            return total
        }
    },
    methods : {
        addEdited(sectionnable){
            sectionnable.edited = true
        },
        toggleEditMode(){
            this.editMode = ! this.editMode
        },
        updateAllEdited(){

            console.log('Hello')
            axios.put('/bon-commande/sectionnables', ['hi', 'hello', 'bjr'] ).then(response => {
                console.log(response)
                // this.$swal({
                //     position: 'top-end',
                //     icon: 'success',
                //     title:  'Votre produit a été modifié avec succès',
                //     showConfirmButton: false,
                //     timer: 1000
                // })
            }).catch(error => {
                console.log(error);
            });
        },
        enableSectionnableEditMode(sectionnable){
            console.log(sectionnable)
            sectionnable.editMode = true;
            this.$forceUpdate()
        },
        formatToPrice(value) {
          return `XAF ${value.toFixed(0)}`;
        },
        updateSectionnable(sectionnable){
            axios.put('/bon-commande/' + sectionnable.pivot.id, sectionnable).then(response => {
                this.$swal({
                    position: 'top-end',
                    width: 300,
                    height: 300,
                    icon: 'success',
                    title:  'Votre produit a été modifié avec succès',
                    showConfirmButton: false,
                    timer: 1000
                })
                sectionnable.editMode = false;
                this.$forceUpdate();
            }).catch(error => {
                console.log(error);
            });
        },
    },
    created(){
        this.bc = this.bc_prop
        this.bc.sectionnables.map( sectionnable => {
            sectionnable.editMode = false
            sectionnable.edited = false
        })
    }
}
</script>
