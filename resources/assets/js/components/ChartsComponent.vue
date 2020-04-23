<template>
    <div id="container">
        <div :id="id" :option="option"></div>
    </div>
</template>

<script>
    import Highcharts from 'highcharts'
    export default {
        name: "charts-component",
        props: {
            id: {
                type: String
            },
            option: {
                type: Object
            }
        },
        mounted() {
            // Highcharts.chart(this.id,this.option)
        },
        methods: {
            show() {
                Highcharts.chart(this.id,this.option)
            }
        }
    }
</script>

<style scoped>
    /*#container{width: 80%;height:  80%}*/
</style>