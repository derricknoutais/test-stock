<script>
export default {
    props: ['products_prop', 'fournisseurs_prop'],
    data(){
        return {
            products: null,
            fournisseurs: null,
            fournisseur_tag: null
        }
    },
    methods:{
        addTag(product){
            axios.post('/product-fournisseur', { product : product}).then(response => {
                console.log(response.data);

            }).catch(error => {
                console.log(error);
            });
        }
    },
    created(){
        this.products = this.products_prop;

        this.fournisseurs = this.fournisseurs_prop;
    }
}
</script>
