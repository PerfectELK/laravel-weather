<template>
    <div class="container">
        <header>
            <div class="city">
                <div class="city__current">{{ city.name }}</div>
                <div class="city__change">
                    <div class="choose-city">
                        <div @click="isCityInputVisible = !isCityInputVisible" class="choose-city__button">
                            Сменить город
                        </div>
                        <div class="choose-city__input-container" v-if="isCityInputVisible">
                            <Autocomplete v-on:submit="citySubmit" :search="citySearch"></Autocomplete>
                        </div>
                    </div>

                    <div @click="setCityByCurrentLocation" class="my-coord">
                        <img src="/img/location.svg" alt="">
                        <p>Моё местоположение</p>
                    </div>
                </div>
            </div>

            <div class="unit">
                <div class="unit__inner">
                    <div
                        v-bind:class="{'unit-item_active': currentUnit === 'c'}"
                        class="unit-item c"
                        @click="changeUnits('c')"
                    >
                        C
                    </div>
                    <div
                        v-bind:class="{'unit-item_active': currentUnit === 'f'}"
                        class="unit-item f"
                        @click="changeUnits('f')"
                    >
                        F
                    </div>
                </div>
            </div>
        </header>

        <main>
            <div class="weather">
                <div class="weather__inner">
                    <div v-if="weather.main.temp" class="temperature">
                        <div class="temperature__value">{{ weather.main.temp | unitFilter(currentUnit) }}º</div>
                    </div>
                    <div class="description">
                        <p v-if="weather.weather[0]" class="description__text">
                            {{ weather.weather[0].description }}
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="footer__inner">
                <div class="state wind">
                    <div class="state__name">
                        Ветер
                    </div>
                    <div v-if="weather.wind.speed" class="state__value">
                        {{ weather.wind.speed }} м/с
                    </div>
                </div>
                <div class="state pressure">
                    <div class="state__name">
                        Давление
                    </div>
                    <div v-if="weather.main.pressure" class="state__value">
                        {{ weather.main.pressure | pressure }} мм рт.ст
                    </div>
                </div>
                <div class="state humidity">
                    <div class="state__name">
                        Влажность
                    </div>
                    <div v-if="weather.main.humidity" class="state__value">
                        {{ weather.main.humidity }}%
                    </div>
                </div>
                <div class="state it-rain-chance">
                    <div class="state__name">
                        Вероятность дождя
                    </div>
                    <div v-if="weather.clouds.all" class="state__value">
                        {{ weather.clouds.all }}%
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<script>
    import axios from 'axios';
    import Cookies from 'js-cookie';
    import Autocomplete from '@trevoreyre/autocomplete-vue'
    import '@trevoreyre/autocomplete-vue/dist/style.css'

    export default {
        data(){
          return {
              coords: [],
              isCoordsAllowed: false,
              isCityInputVisible: false,
              currentUnit: 'c',
              cities: [],
              city: {
                  featureName: false,
                  name: 'Не определено',
                  inputVal: '',
              },
          }
        },
        asyncComputed: {
            weather: {
                lazy: true,
                async get() {
                    const feature = this.city.featureName;
                    if (!feature) {
                        return {};
                    }
                    const weather = await this.weatherGet(feature);
                    return weather;
                },
                default: {
                    main: {
                        temp: ''
                    },
                    clouds: {},
                    wind: {},
                    weather: [],
                }
            }
        },
        filters: {
            unitFilter(value, currentUnit) {
                if (!value) {
                    return '';
                }
                value = Math.floor(value);
                if (currentUnit === 'c') {
                    return value;
                }
                return Math.floor((value * (9/5)) + 32);
            },
            pressure(value) {
                if (!value) {
                    return '';
                }
                return Math.floor(parseFloat(value) / 1.333);
            }
        },
        methods: {
            async setCityByCurrentLocation() {
                navigator.geolocation.getCurrentPosition(async (pos) => {
                    this.isCoordsAllowed = true;
                    this.coords = [
                        pos.coords.latitude,
                        pos.coords.longitude,
                    ]
                    await this.getCityByCoords();
                });
            },
            async getCityByCoords() {
                const apiResponse = await axios.get(`/api/cities/geo/reverse/${this.coords[0]}/${this.coords[1]}`);
                this.city.featureName = apiResponse.data[0].local_names.feature_name;
                this.city.name = apiResponse.data[0].local_names.ru;
                this.citiesToCookies();
             },
            async weatherGet(feature) {
                const apiResponse = await axios.get(`/api/cities/weather/${feature}`);
                return apiResponse.data;
            },
            citiesToCookies() {
                Cookies.set('featureName', this.city.featureName);
                Cookies.set('cityName', this.city.name);
            },
            changeUnits(unit) {
                this.currentUnit = unit;
            },
            async citySearch(input) {
                if (input.length < 3) {
                    return [];
                }
                const apiResponse = await axios.get(`/api/cities/${input}/`);
                this.cities = apiResponse.data;
                return this.cities.map((item) => {
                    return item.name;
                });
            },
            citySubmit(result) {
                if (!result) {
                    return;
                }
                const featureName = this.cities.find((item) => {
                    return item.name === result
                }).slug;
                this.city.featureName = featureName;
                this.city.name = result;
                this.isCityInputVisible = false;
            }
        },
        components: {
          Autocomplete,
        },
        mounted() {
            const featureName = Cookies.get('featureName');
            const cityName = Cookies.get('cityName');
            if (featureName && cityName) {
                this.city.featureName = featureName;
                this.city.name = cityName;
            } else {
                this.setCityByCurrentLocation();
            }
        }
    }
</script>

<style scoped lang="scss">
    .container {
        background-color: #498CEC;
        padding: 100px;
        height: calc(100vh - 200px);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        header{
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            .city {
                &__current {
                    color: #fff;
                    font-size: 50px;
                }
                &__change {
                    padding-top: 10px;
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    gap: 20px;
                    .choose-city {
                        position: relative;
                        &__button {
                            color: #fff;
                            font-size: 18px;
                            cursor: pointer;
                            opacity: 0.6;
                        }
                        &__input-container {
                            position: absolute;
                            bottom: -120%;
                            left: 0;
                            transform: translateY(50%);
                            width: 300px;
                        }
                    }
                    .my-coord {
                        display: flex;
                        flex-direction: row;
                        align-items: center;

                        p {
                            color: #fff;
                            font-size: 18px;
                            cursor: pointer;
                            opacity: 0.6;
                        }
                    }
                }
            }
            .unit {
                &__inner {
                    display: flex;
                    flex-direction: row;
                    justify-content: space-around;
                    align-items: center;
                    width: 77px;
                    height: 29px;
                    .unit-item {
                        font-size: 18px;
                        color: #fff;
                        flex-grow: 1;
                        flex-shrink: 0;
                        height: 100%;
                        display: flex;
                        flex-direction: row;
                        justify-content: center;
                        align-items: center;
                        cursor: pointer;
                        opacity: 0.2;
                        border: 1px solid #FFFFFF;
                        border-radius: 7px;
                        &_active {
                           opacity: 1;
                        }
                        &.c {
                            border-bottom-right-radius: 0px;
                            border-top-right-radius: 0px;
                        }
                        &.f {
                            border-bottom-left-radius: 0px;
                            border-top-left-radius: 0px;
                        }
                    }
                }
            }
        }
        main {
            .weather {
                &__inner {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    .temperature {
                        &__value {
                            font-size: 180px;
                            line-height: 140px;
                            color: #fff;
                        }
                    }
                    .description {
                        &__text {
                            font-size: 25px;
                            color: #fff;
                        }
                    }
                }
            }
        }
        .footer {
            &__inner {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                .state {
                    &__name {
                        font-size: 18px;
                        color: #fff;
                        opacity: 0.6;
                    }
                    &__value {
                        font-size: 25px;
                        color: #fff;
                    }
                }
            }
        }
    }
</style>
