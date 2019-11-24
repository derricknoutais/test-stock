

<script>
export default {
    props: ['template_prop', ],
    data(){
        return {
            selected_product: false,
            template: null
        }
    },
    methods:{
        addProduct(){
            var data = [];
            this.selected_product.forEach(product => {
                data.push({template_id : this.template.id, product_id : product.id })
            });
            axios.post('/product-template', data ).then(response => {

                if(response.data === 'OK'){
                    this.selected_product.forEach( element => {
                        this.template.products.unshift(element)
                    })
                    
                    this.selected_product = false
                    this.$refs.searchBar.focus()
                }

            }).catch(error => {
                console.log(error);
            });
        },
        removeProduct(index){
            axios.post('/product-template/delete', {template_id : this.template.id, product_id : this.template.products[index].id } ).then(response => {
                if(response.data === 'OK'){
                    this.template.products.splice(index, 1)
                }
            }).catch(error => {
                console.log(error);
            });
        }

    },
    created(){
        this.template = this.template_prop
    }
}
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>