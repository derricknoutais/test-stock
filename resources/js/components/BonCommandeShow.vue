
<script>

export default {
    props: [ 'bc_prop' ],

    data(){
        return {
            bc: null,
            editMode : false,
            newProduct: null,
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
        addNewProduct(){
            if(this.newProduct){
                axios.post('/bon-commande/sectionnable', { product: this.newProduct, bc: this.bc, }).then(response => {
                    console.log(response.data);
                    window.location.reload()
                }).catch(error => {
                    console.log(error);
                });
            }
        },
        toggleEditMode(){
            this.editMode = ! this.editMode
        },
        updateAllEdited(){

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
        updateSectionnable(sectionnable, location){
            axios.put('/' + location + '/' + sectionnable.pivot.id, sectionnable).then(response => {
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
        deleteSectionnable(sectionnable, location){
            console.log('hello')
            this.$swal({
                title: 'Êtes-vous sûr(e)?',
                  text: "Cette action est irréversible!",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Oui, Supprimer!',
                  cancelButtonText: 'Annuler',
                }).then((result) => {
                  if (result.value) {
                      axios.delete('/' + location + '/sectionnable/' + sectionnable.pivot.id ).then( response => {
                          this.$swal(
                            'Deleted!',
                            'Your file has been deleted.',
                            'success'
                          )
                          console.log(response.data);
                      }).catch( error =>{
                          console.log(error)
                      })


                  }
            })
        },
        createInvoice(){
            axios.get('/bon-commande/' + this.bc.id + '/create-invoice').then(response => {
                console.log(response.data);

            }).catch(error => {
                console.log(error);
            });
        }
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
