
<script>
export default {
    props: [ 'bc_prop' ],
    data(){
        return {
            bc: null
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
    created(){
        this.bc = this.bc_prop
    }
}
</script>