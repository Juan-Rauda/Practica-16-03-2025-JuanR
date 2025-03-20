<template>
    <div class="container">
        <div class="filtros mb-4">
            <div class="row">
                <div class="col-md-3">
                    <select v-model="filtros.category_id" class="form-control">
                        <option value="">Todas las categorías</option>
                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                            {{ categoria.name }}
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select v-model="filtros.product_id" class="form-control">
                        <option value="">Todos los productos</option>
                        <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                            {{ producto.name }}
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" v-model="filtros.my_orders" id="misPedidos">
                        <label class="form-check-label" for="misPedidos">Mis pedidos</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Productos</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="pedido in pedidos" :key="pedido.id">
                        <td>{{ pedido.id }}</td>
                        <td>{{ pedido.order_date }}</td>
                        <td>{{ pedido.customer }}</td>
                        <td>
                            <div v-for="producto in pedido.products" :key="producto.name">
                                {{ producto.name }} ({{ producto.category }}) - 
                                Cantidad: {{ producto.quantity }} - 
                                Precio: ${{ producto.price }}
                            </div>
                        </td>
                        <td>${{ pedido.total }}</td>
                    </tr>
                    <tr v-if="pedidos.length === 0">
                        <td colspan="5" class="text-center">No se encontraron pedidos</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            pedidos: [],
            categorias: [],
            productos: [],
            filtros: {
                category_id: '',
                product_id: '',
                my_orders: false
            }
        }
    },
    methods: {
        async fetchOrders() {
            try {
                const response = await axios.get('/api/pedidos', {
                    params: this.filtros
                });
                this.pedidos = response.data;
            } catch (error) {
                console.error('Error al obtener pedidos:', error);
            }
        },
        async fetchCategories() {
            try {
                const response = await axios.get('/api/catalogo/categoria');
                this.categorias = response.data;
            } catch (error) {
                console.error('Error al obtener categorías:', error);
            }
        },
        async fetchProducts() {
            try {
                const response = await axios.get('/api/catalogo/productos');
                this.productos = response.data;
            } catch (error) {
                console.error('Error al obtener productos:', error);
            }
        }
    },
    watch: {
        filtros: {
            deep: true,
            handler() {
                this.fetchOrders();
            }
        }
    },
    mounted() {
        this.fetchCategoria();
        this.fetchProductos();
        this.fetchOrders();
    }
}
</script>
