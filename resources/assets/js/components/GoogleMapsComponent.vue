<template>
    <div id="app">
        <el-dialog :visible.sync="openMap" @close="closeMap">
            <google-map :center="center" :zoom="zoom" style="width: 100%; height: 500px" @click="mapClick">
                <google-marker :position="center" :clickable="true"
                               :draggable="true" @click="mapClick" >
                </google-marker>
            </google-map>
            <el-button @click="closeMap">取 消</el-button>
            <el-button type="primary" @click="closeMap(1)">確 定</el-button>
        </el-dialog>
    </div>
</template>

<script>
    import * as VueGoogleMaps from 'vue2-google-maps'
    import Cookies from '../tools/vue-cookies';
    import Tools from '../tools/vue-common-tools';

    Vue.use(VueGoogleMaps, {
        load: {
            key: Cookies.getCookie('google_map_key'),
            libraries: 'places',
        },
    });

    export default {
        name: "GoogleMapsComponent",
        components:{'google-map': VueGoogleMaps.Map,'google-marker': VueGoogleMaps.Marker},

        data: function () {
            return {
                loading: true,
                center: {
                    lat: 22.761249763869124,
                    lng: 121.14370029709289
                },
                select: {
                    lat: 0,
                    lng: 0
                },
                mapKey:"",
                zoom:17,
                centers: {
                    '1':{
                        lat: 22.761249763869124,
                        lng: 121.14370029709289
                    },
                    '2':{
                        lat: 23.1057924,
                        lng: 121.2204712
                    },
                    '3':{
                        lat: 23.049205,
                        lng: 121.164799
                    },
                    '4':{
                        lat: 23.317386,
                        lng: 121.454738
                    },
                    '5':{
                        lat: 23.130544,
                        lng: 121.175795
                    },
                    '6':{
                        lat: 23.120845,
                        lng: 121.215864
                    },
                    '7':{
                        lat: 22.971365,
                        lng: 121.304896
                    },
                    '8':{
                        lat: 22.899914,
                        lng: 121.108465
                    },
                    '9':{
                        lat: 22.911057,
                        lng: 121.049510
                    },
                    '10':{
                        lat: 22.784889,
                        lng: 121.083353
                    },
                    '11':{
                        lat: 22.559293,
                        lng:  120.873133
                    },
                    '12':{
                        lat: 22.610125,
                        lng: 121.004650
                    },
                    '13':{
                        lat: 22.384404,
                        lng: 120.910985
                    },'14':{
                        lat: 22.370690,
                        lng: 120.849206
                    },'15':{
                        lat: 22.660593,
                        lng: 121.491669
                    },'16':{
                        lat: 22.046491,
                        lng:  121.545069
                    },'17':{
                        lat: 22.761249763869124,
                        lng: 121.14370029709289
                    },
                },
                openMap: false
            }
        },
        methods: {
            mapClick(event) {
                let lat = event.latLng.lat();
                let lng = event.latLng.lng();
                this.select = this.center = {
                    lat: lat,
                    lng: lng
                };
            },

            closeMap(type=0) {
                if(!this.openMap)
                    return
                this.openMap = false;
                if(type===0) {
                    this.center= {
                        lat: 22.761249763869124,
                            lng: 121.14370029709289
                    }
                    this.select= {
                        lat: 0,
                        lng: 0
                    }
                }else{
                    this.$emit('select', this.select)
                }
            },
            
            selectMap(area, address) {
                if (address) {
                    let geocoder = new google.maps.Geocoder();
                    geocoder.geocode({'address': address}, (results, status) => {
                        if (status === google.maps.GeocoderStatus.OK) {
                            this.openMap = true;

                            this.center.lat = results[0].geometry.location.lat();
                            this.center.lng = results[0].geometry.location.lng();
                            this.select = this.center;
                        } else {
                            Tools.Dialog(this).openError(null, '請填寫正確地址');
                        }
                    });

                    return;
                }

                if (parseInt(area) === 1 || parseInt(area) === 17) {
                    this.zoom = 16;
                } else {
                    this.zoom = 13;
                }
                this.center = this.centers[area];
                this.openMap = true;
            },

            selectMapByPosition(lat, lng) {
                this.zoom = 14;
                this.center.lat = lat;
                this.center.lng = lng;
                this.openMap = true;
                this.select = this.center;
            },

            getSelect(reset) {
                let position = {};
                Tools.coverObj(position, this.select);
                if(reset) {
                    this.select = {
                        lat:0,
                        lng:0
                    }
                }

                return position;
            }
        }
    }
</script>

<style scoped>

</style>