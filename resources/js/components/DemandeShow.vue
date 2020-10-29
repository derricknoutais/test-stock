<script>
export default {
    props : ['demande_prop', 'demandes_prop'],
    data(){
        return {
            demande: null,
            demandes: this.demandes_prop,
            cardNumber: null,
            options: {
              creditCard: true,
              delimiter: '-',
            },
            sectionnable_being_deleted: null,
            sectionnable_being_moved: null,
            demande_to_move_to: null,
            detailsState: true
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
            if(sectionnable.pivot.offre <= 0){
                sectionnable.hasError = true
                this.$forceUpdate()
            } else {
                sectionnable.hasError = false
                this.$forceUpdate()
                axios.put('/demande/' + this.demande.id + '/update-product', sectionnable).then(response => {
                    console.log(response.data);

                }).catch(error => {
                    console.log(error);
                });
            }
        },
        openDeleteModal(sectionnable){
            this.sectionnable_being_deleted = sectionnable
            $('#delete-modal').modal('show')
        },
        removeSectionnable(){
            axios.delete('/demande-sectionnable/' + this.sectionnable_being_deleted.pivot.id).then(response => {
                console.log(response.data);
                var index = this.demande.sectionnables.indexOf(this.sectionnable_being_deleted)
                this.demande.sectionnables.splice(index, 1)
                this.sectionnable_being_deleted = null
                $('#delete-modal').modal('hide')
            }).catch(error => {
                console.log(error);
            });
        },
        normaliserQuantités(){
            this.demande.sectionnables.forEach( sectionnable => {
                this.enregisterOffre(sectionnable)
                sectionnable.pivot.quantite_offerte = sectionnable.quantite
            })
            this.$forceUpdate()
        },

        editMode(sectionnable){
            sectionnable.editing = true
            this.$forceUpdate()
        },
        editTraduction(sectionnable){

            if (! sectionnable.pivot.traduction) {

                axios.put('/demande-sectionnable', sectionnable ).then(response => {

                    sectionnable.pivot.traduction = [
                        sectionnable.product.handle.translation, sectionnable.product[sectionnable.product.handle.display1],
                        sectionnable.product[sectionnable.product.handle.display2], sectionnable.product[sectionnable.product.handle.display3]
                    ].filter(Boolean).join(' / ', '');

                    this.$forceUpdate()

                }).catch(error => {
                    console.log(error);
                });
            }
            sectionnable.editing = true
            this.$forceUpdate()
        },
        saveTraduction(sectionnable){

            axios.patch('/demande-sectionnable-traduction', sectionnable)
            .then(response => {
                sectionnable.editing= false
                this.$forceUpdate()
            })
            .catch(error => {
                console.log(error);
            });
        },
        toggleDetails(sectionnable){
            sectionnable.displayDetails = ! sectionnable.displayDetails;
            this.$forceUpdate()
        },
        ajouterSectionnableABonCommande(element, index){
            element.demande_id = element.pivot.demande_id
            element.offre = element.pivot.offre
            element.quantite_offerte = element.pivot.quantite_offerte
            element.sectionnable_id = element.pivot.sectionnable_id

            axios.post('/commande/' + this.demande.commande_id + '/résoudre-conflit',
                {element : element}
            ).then(response => {

            }).catch(error => {
                console.log(error);
            });
        },
        openMoveModal(sectionnable){
            this.sectionnable_being_moved = sectionnable
            // $('#demande-move-modal').modal('show')
            this.updateSectionnable(this.sectionnable_being_moved, 'demande_id', 190)
        },
        toggleAllDetails(){
            this.detailsState = ! this.detailsState
            this.demande.sectionnables.map( sect => {
                sect.displayDetails = this.detailsState;
                this.$forceUpdate()
            })
            this.$forceUpdate()
        },
        deplacerSectionnable(){
            this.updateSectionnable(this.sectionnable_being_moved, 'demande_id', this.demande_to_move_to.id )
        },
        updateSectionnable(sectionnable, field, value){
            axios.patch('/demande-sectionnable', {id: sectionnable.pivot.id, field: field, value: value}).then(response => {
                console.log(response.data);
                sectionnable[field] = value;
                sectionnable.editing = false
                this.$forceUpdate()

            }).catch(error => {
                console.log(error);
            });
        }
    },
    created(){
        this.demande = this.demande_prop
        this.demande.sectionnables.map( sect => {
            sect.editing = false
            sect.displayDetails = true;
        })
    }
}
</script>
