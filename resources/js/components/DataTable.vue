<template>
    <div>
        <v-toolbar flat color="white">
            <v-toolbar-title>
                <slot name="title"></slot>
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-text-field
                v-model="search"
                append-icon="search"
                label="Buscar"
                single-line
                hide-details
                >
            </v-text-field>
            
            <slot name="actions"></slot>
            
            <template v-if="urlCreate">
                
                <v-spacer></v-spacer>
                <a class="btn btn-outline-primary btn-sm float-right" :href="urlCreate">
                    <i class="fas fa-plus-circle"></i>
                    <span class="btn-inner--text">Nueva</span>
                </a>
            </template>
        </v-toolbar>
        
        <div class="row justify-content-md-center mb-10" v-if="actions != undefined && selected.length > 0">
          <div class="col-md-auto">
            <button v-for="action in actions" :key="action.text" :class="action.class" @click="bindAction(action)" >
              <span v-if="action.icon.length > 0" :class="action.icon"></span>
              {{ action.text }}
            </button>
          </div>
        </div>
      
        <v-data-table
            :headers="headers_"
            :items="data_"
            :pagination.sync="pagination"
            :total-items="totalData_"
            :loading="loading"
            :select-all="checking"
            v-model="selected"
            :item-key="checking_key"
            class="elevation-1">

            
            <!--<template v-slot:actions-prepend  >
              <a  @click.prevent.self="downloadExcel" href="#"><i class="fas fa-file-excel"></i> Exportar a excel</a>
            </template>-->
            <template v-slot:headers="props">
              <tr>
                <th v-if="checking">
                  <v-checkbox
                    :input-value="props.all"
                    :indeterminate="props.indeterminate"
                    primary
                    hide-details
                    @click.stop="toggleAll"
                  ></v-checkbox>
                </th>
                <th
                  v-for="header in props.headers"
                  :key="header.text"
                  :class="[
                    header.sortable ? 'column sortable':'', 
                    pagination.descending ? 'desc' : 'asc', 
                    header.value === pagination.sortBy ? 'active' : ''
                  ]"
                  @click="changeSort(header.value, (header.sortable && !header.has_filter))"
                >
                  <v-icon small v-if="header.sortable">arrow_upward</v-icon>
                  <button 
                    class="btn btn-default" 
                    @click="changeSort(header.value, true)" 
                    v-if="header.sortable && header.has_filter">
                    <i class="fas fa-sort"></i>
                  </button>
                  
                  <span  v-if="!header.has_filter">{{ header.text }}</span>
                  <slot v-if="header.has_filter" :update="fetchData" :header="header" :name="'filter_' + header.value" ></slot>
                </th>
                
                <!--<slot name="header" v-bind:header="props.headers">
                  <th v-for="header in headers" :key="header.value">
                    {{ header.text }}
                  </th>
                </slot>-->
                <!--<slot name="header" v-bind:headers="props.headers" >
                </slot>-->
                
              </tr>
              
            </template>
            
            <template v-slot:items="props">
                <tr :active="props.selected" @click="props.selected = !props.selected">
                  <td v-if="checking">
                    <v-checkbox
                      :input-value="props.selected"
                      primary
                      hide-details
                    ></v-checkbox>
                  </td>
                  <td v-for="item in headers_" :key="item.value">
                    <span v-if="!item.slot">
                      {{ getValue(props.item, item.value) }}
                    </span>
                    <span v-else>
                      <slot :name="item.slot" :item="props.item"></slot>
                    </span>
                  </td>
                </tr>
            </template>


            <template v-slot:no-data >
              <p class="text-center text-muted">
                - No se encontró información para mostrar -
              </p>
            </template>
            <template v-slot:no-results>
              <p class="text-center text-muted">
                - No se encontró información que coincida con "<strong>{{ search }}</strong>" -
              </p>
              <v-alert :value="true" color="error" icon="warning">
                Your search for "{{ search }}" found no results.
              </v-alert>
            </template>
        </v-data-table>
    </div>
</template>
<script>
  export default {
    
    props: {
        urlCreate: String,
        urlApi: String,
        headers: Array,
        actions: Array
    },
    data () {
      return {
        search: null,
        headers_: [],
        data_: [],
        pagination: {},
        loading: true,
        totalData_: 0,
        has_filters: false,
        filters: {},
        checking: undefined,
        checking_key: null,
        selected: [],
      }
    },
    watch: {
      pagination: {
        handler () {
          this.fetchData();
        },
        deep: true
      },
      search: {
        handler () {
          this.searchData();
        },
        deep: true
      },
      headers: {
        handler(){
          this.updateHeaders();
        },
        deep: true
      }
    },
    mounted(){
      this.fetchData();
      if(this.$attrs['checking'] !== undefined){
        this.checking = true;  
        this.checking_key = this.$attrs['checking'];
      }
    },
    created(){
      this.updateHeaders();
    },
    methods: {
      searchData: _.debounce(
          function(next){
              this.fetchData();
          }, 500
      ),
      existSlot(name){
        return !!this.$slots[name]
      },
      getValue(object, attribute){
        return attribute.split('.').reduce(function(prev, curr) {
            return prev ? prev[curr] : null
        }, object || self);
      },
      updateHeaders(){
        let el = this;
        el.headers_ = [];
        _.forEach(this.headers, function(item) {
          
          if( item.sortable == undefined )
          {
            item.sortable = false;
          }
          el.headers_.push(item)
        });
        this.has_filters = _.filter(this.headers_, ['has_filter', true]).length > 0;
        

        _.forEach(this.headers_, function(o){
          if( o.has_filter == true ){ 
            o.filter = null;
          }
        });


      },
      downloadExcel(){
        let el = this;
        axios({
          url: el.urlApi,
          method: 'GET',
          responseType: 'blob', // important
          params: {
            query: el.search,
            headers: el.headers_,
            pagination: el.pagination,
            excel: true
          }
        }).then((response) => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', 'file.xlsx');
          document.body.appendChild(link);
          link.click();
        });
      },
      fetchData(){
        if( !this.urlApi ){
          this.loading = false;
          return true;
        }


        let el = this;
        el.data_ = [];
        axios.get(el.urlApi, {
          params: {
            query: el.search,
            headers: el.headers_,
            pagination: el.pagination
          }
        }).then(response => {
          el.data_ = response.data.items;
          el.totalData_ = response.data.total;
        }).finally(function(){
          el.loading = false;
        })

        
      },
      changeSort (column, sortable) {
        if( !sortable )
          return true;
        if (this.pagination.sortBy === column) {
          this.pagination.descending = !this.pagination.descending
        } else {
          this.pagination.sortBy = column
          this.pagination.descending = false
        }
      },
      toggleAll(){
        if (this.selected.length) this.selected = []
        else this.selected = this.data_.slice()
      },
      bindAction(action)
      {
        let el = this;
        if( action.confirmText != undefined )
        {
           Swal.fire({
              title: action.text,
              text: action.confirmText,
              type: 'warning',
              showCancelButton: true,
              reverseButtons: true,
              cancelButtonText: 'Salir',
              confirmButtonText: 'Aceptar',
              buttonsStyling: false,
              customClass: {
                  confirmButton: 'btn btn-outline-danger',
                  cancelButton: 'btn btn-default'
              },
              showLoaderOnConfirm: true
          }).then((result) => {
              if (result.value) {
                  el.showLoader();
                  axios.post(action.url, { selected: el.selected } ).then(response => {
                    el.hideLoader();
                    el.fetchData();
                    Swal.fire(
                        '¡Muy bien!',
                        response.data.message,
                        'success'
                    );
                  }).catch(error => {
                      el.hideLoader();
                      let message = 'Ocurrio un error, por favor válida tus datos.';
                      if( error.response.status == 400 )
                      {
                          message = error.response.data.message;
                      }
                      Swal.fire(
                          '¡Ups!',
                          message,
                          'error'
                      );
                  });
              }
          });
        }
      }
    }
  }
</script>