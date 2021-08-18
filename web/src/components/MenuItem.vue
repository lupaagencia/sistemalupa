<template>
    <div  :class="depth >= 1 ? 'tree-view-child' : 'depth ' + depth">
        
        <h4 class="panel-title">
            <div @click="[showSublevel()]"  :class="filterCat == id ? 'active' : ''">
                <span v-if="nodes" class="badge pull-right"><i class="fa fa-plus"></i></span>
                <p :style="indent" @click="updateFilterCat(id)">{{label}}</p>
            </div>
            <collapse-transition group tag="div" >
            <menu-item :key="node.id" :id="node.id" v-if="showChildren" 
                v-for="node in nodes" :label="node.name" 
                :nodes="node.sublevels" :depth="depth + 1"  
                > 
            </menu-item>
            </collapse-transition>
        </h4>

    </div>
</template>

<script>
    import { mapMutations,mapState } from 'vuex'
    import { CollapseTransition } from 'vue2-transitions'
    export default {
        name: 'menu-item',
        components : {
            CollapseTransition
        },
        props: [
            'label',
            'nodes',
            'depth',
            'id',
        ],
        data() {
            return {
                showChildren: false,
            }
        },
        watch:  {
            filterCat() {
                if(!this.nodes && this.id == this.filterCat){
                        this.$store.commit('showSearch')
                }else if(this.$store.state.searchVisible  && this.nodes){
                        this.$store.commit('hideSearch')
                }
            }
        },
        methods: {
            showSublevel() {
                this.showChildren = !this.showChildren
            },
            ...mapMutations(['updateFilterCat'])
        },
        computed: {
            indent() {
                return {
                    transform: `translate(${this.depth * 20}px)`
                }
            },
             ...mapState(['filterCat']),
                getProductByCat: {
                    set(value) {
                        this.$store.commit('updateFilterCat',value)
                    }
         },
        }
    }
</script>

<style>
</style>