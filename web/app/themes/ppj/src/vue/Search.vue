<template>
        <div class="search" v-cloak>
            <h2 class="search__title">{{ titleText }}</h2>
            <p>Location (postcode, town, region)</p>
            <form class="search__form">
                <input type="text"
                       class="search__input"
                       :placeholder="placeHolderText"
                       editable="editable"
                       v-model="searchResults.searchTerm" />

                <button class="search__button-search"
                        @click.stop.prevent="search"
                        :disabled="searchResults.searchTerm == ''" >
                    <div class="search__button-search-circle"></div>
                    <div class="search__button-search-rectangle"></div>
                </button>

                <div class="search__results" v-show="searchResults.display">

                    <div class="search__view-container">
                        <div class="search__map-view" v-show="searchResults.activeView == 0">
                            <div class="search__map-container">
                              <div class="search__square-box">
                                <div class="search__map"></div>
                              </div>
                            </div>
                            <div class="search__view-list-container">
                              <div class="search__rectangle">
                                <ul class="search__view-list">
                                  <li class="search__view-list-element">Results will appear here</li>
                                  <li class="search__view-list-element"
                                        v-for="(job, index) in searchResults.jobLocationGroups[searchResults.selectedJobLocationGroupId]"
                                        :key="index" >
                                        <job-summary :distance="job.distance"
                                                     :distance-time="job.distanceTime"
                                                     :position="job.title"
                                                     :salary="job.salary"
                                                     :prison-name="job.prison_name"
                                                     :prison-city="job.organizationCity"
                                                     :prison-page-link="job.url"
                                                     :url="job.url"
                                                     :selected="true"
                                        >
                                        </job-summary>
                                    </li>
                                  <li class="search__view-list-element"
                                      v-for="(job, index) in visibleSearchResults"
                                      :key="index" >
                                      <job-summary :distance="job.distance"
                                                   :distance-time="job.distanceTime"
                                                   :position="job.title"
                                                   :salary="job.salary"
                                                   :prison-name="job.prison_name"
                                                   :prison-city="job.organizationCity"
                                                   :prison-page-link="job.url"
                                                   :url="job.url" >
                                      </job-summary>
                                  </li>
                                </ul>
                              </div>
                            </div>
                        </div>
                        <div class="search__list-view" v-show="searchResults.activeView == 1">
                            <ul class="search__list-view-list">
                                <li v-for="(job, index) in visibleSearchResults"
                                    :key="index" >
                                    <job-summary :distance="job.distance"
                                                 :distance-time="job.distanceTime"
                                                 :position="job.title"
                                                 :salary="job.salary"
                                                 :prison-name="job.prison_name"
                                                 :prison-city="job.organizationCity"
                                                 :prison-page-link="job.url"
                                                 :url="job.url" >
                                    </job-summary>
                                </li>
                            </ul>
                            <div class="search__pagination">
                                <button class="search__pagination-direction"
                                        :class="{'search__pagination-direction--active': (backwardEnabled == true)}"
                                        @click.stop.prevent="showPreviousPage"
                                > < </button>

                                <button class="search__pagination-direction"
                                        :class="{'search__pagination-direction--active': (forwardEnabled == true)}"
                                        @click.stop.prevent="showNextPage"
                                > > </button>

                                <button v-for="n in numberOfResultPages"
                                        class="search__pagination-link"
                                        :class="{'search__pagination-link--active': (searchResults.listView.activePage == (n - 1))}"
                                        @click.stop.prevent="showPage(n-1)"
                                >{{ n }}</button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
</template>

<script>
    import _ from 'lodash';
    import dummyJobs from '../js/dummyJobs';
    import CustomMarker from '../js/CustomMarker';

    function getFirstElementInObject(obj) {
        return obj[Object.keys(obj)[0]];
    }

    export default {
        props: {
          'default-search-term': {
              default: '',
              type: String
          }
        },

        data() {
            return {
                searchResults: {
                    activeView: 0,
                    display: true,//false,
                    postCode: this.defaultSearchTerm,//'',//'SW1H 1AJ',
                    urlEncodedPostCode: '',
                    googleMapAPIKey: 'AIzaSyDDplfBkLzNA3voskfGyExYnQ46MJ0VtpA',
                    listView: {
                        activePage: 0,
                        resultsPerPage: 5,
                        forwardEnabled: true,
                        backwardEnabled: false
                    },
                    jobs: dummyJobs,
                    orderBy: 'distance',
                    //orderedJobs: dummyJobs,
                    jobLocationGroups: {},
                    selectedJobLocationGroupId: '',
                    visibleJobLocationGroup: null
                },
                mapSrc: '',
                mapOptions: {
                    zoom: 6,
                    center: new google.maps.LatLng(52.4832138,-1.4947146),
                    disableDefaultUI: false,
                    streetViewControl: false,
                    mapTypeControl: false,
                },
                titleText: 'Search for jobs',
                placeHolderText: 'Enter location'
            }
        },

        computed: {
            numberOfResultPages: function() {
                const num = Math.ceil(this.searchResults.jobs.length / this.searchResults.listView.resultsPerPage);
                return num;
            },

            visibleSearchResults: function() {
                console.log('computing visible search results');
                if (window.innerWidth < 768) {

                  const
                      listView = this.searchResults.listView,
                      startIndex =
                            listView.activePage
                          * listView.resultsPerPage,
                      endIndex = startIndex + listView.resultsPerPage
                  ;
                  const orderedJobs = _.orderBy(this.searchResults.jobs, [this.searchResults.orderBy]);
                  return orderedJobs.slice(startIndex, endIndex);

                } else {
                  return this.searchResults.jobs;
                }
            },

            defaultSearchResults: function() {
              return this.searchResults.jobs;
            },

            backwardEnabled: function() {
                return (this.searchResults.listView.activePage > 0);
            },

            forwardEnabled: function() {
                return (this.searchResults.listView.activePage < (this.numberOfResultPages - 1));
            }
        },

        watch: {
            selectedJobLocationGroupId: function(selectedJobLocationGroupId) {
                if (selectedJobLocationGroupId) {
                    this.searchResults.visibleJobLocationGroup = searchResults.jobLocationGroups[selectedJobLocationGroupId];
                }
            },

            'searchResults.activeView': function() {
                console.log('watch - searchResults.activeView');
            }
        },

        methods: {
            toRadians(degrees) {
              return degrees * (Math.PI / 180);
            },
            calculateDistanceBetweenTwoLatLngPoints(lat1, lng1, lat2, lng2) {

                const
                    R = 6371 // kilometres
                  * 0.621371 // kilometres per mile
                  ,
                    φ1 = this.toRadians(lat1),
                    φ2 = this.toRadians(lat2),
                    Δφ = this.toRadians(lat2-lat1),
                    Δλ = this.toRadians(lng2-lng1)
                ;

                const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);

                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

                const d = R * c;
                console.log('d ', d);
                return d;
            },
            updateSelectedJobLocationGroupId(id) {
                this.searchResults.selectedJobLocationGroupId = id;
            },

            updateMapWithJobLocationGroupMarkers(jobLocationGroups) {
                const markerArgs = [];
                for (let group in jobLocationGroups) {
                    markerArgs.push({
                        class: 'search__map-marker--job-location-group',
                        solid: true,
                        amount: jobLocationGroups[group].length,
                        groupId: group,
                        clickCallback: this.updateSelectedJobLocationGroupId.bind(this, group)
                    });
                }
                for (let i in markerArgs) {
                    if (markerArgs[i].groupId == this.searchResults.selectedJobLocationGroupId) {
                        markerArgs[i].selected = true;
                    }
                    const latLngArr = markerArgs[i].groupId.split(',');
                    const latLng = new google.maps.LatLng(latLngArr[0], latLngArr[1]);
                    new CustomMarker(
                        latLng,
                        this.map,
                        markerArgs[i]
                    );
                }
            },

            updateJobsDistance(jobs, lat, lng) {
                for (let i = 0; i < jobs.length; i++) {
                    const job = jobs[i];
                    //job.distance = parseFloat(parseFloat(el.distance.text).toFixed(1));
                    job.distance =  this.calculateDistanceBetweenTwoLatLngPoints(lat, lng, job.prison_location.lat, job.prison_location.lng);
                }

                jobs = _.orderBy(jobs, [this.searchResults.orderBy]);
            },

            createJobLocationGroups() {
                console.log('createJobLocationGroups');

                const jobs = this.searchResults.jobs;
                const jobLocationGroups = {};
                let closestJobLocationGroupDistance = Number.MAX_SAFE_INTEGER;
                let closestJobLocationGroupId = null;

                for (let i = 0; i < jobs.length; i++) {
                    const latLngStr = jobs[i].prison_location.lat + ',' + jobs[i].prison_location.lng;

                    if (typeof jobLocationGroups[latLngStr] == 'undefined') {
                        jobLocationGroups[latLngStr] = [jobs[i]];
                    } else {
                        jobLocationGroups[latLngStr].push(jobs[i]);
                    }

                    if (jobs[i].distance < closestJobLocationGroupDistance) {

                      closestJobLocationGroupDistance = jobs[i].distance;
                      console.log('closest job group distance so far is ', closestJobLocationGroupDistance);
                      closestJobLocationGroupId = latLngStr;
                      console.log('closest job group so far is ', closestJobLocationGroupId);
                    }
                }
                this.searchResults.jobLocationGroups = jobLocationGroups;
                this.searchResults.selectedJobLocationGroupId = closestJobLocationGroupId;
            },

//            handleDistanceMatrixData(response, status) {
//                if (status != google.maps.DistanceMatrixStatus.OK) {
//                    // TODO handle no distance matrix response
//                    console.error('handleDistanceMatrixData status not OK');
//                } else {
//                    if (response.rows[0].elements[0].status === "ZERO_RESULTS") {
//                        console.error('distance matrix: zero results'); // TODO handle zero results
//                    } else {
//                        this.updateJobsWithDistanceMatrixData(this.searchResults.jobs, response.rows[0].elements);
//                        this.createJobLocationGroups();
//                        this.searchResults.display = true;
//
//                        // create map now that containing div is visible
//                        this.map = new google.maps.Map(
//                            document.getElementsByClassName('search__map')[0]
//                            , this.mapOptions
//                        );
//
//                        // create map markers
//                        var marker = new google.maps.Marker({
//                            position: this.mapOptions.center,
//                            map: this.map,
//                            label: this.searchTerm
//                        });
//                        this.updateMapWithJobLocationGroupMarkers(this.searchResults.jobLocationGroups);
//                    }
//                }
//            },
            createMap() {
              this.map = new google.maps.Map(
                document.getElementsByClassName('search__map')[0]
                , this.mapOptions
              );
              this.createJobLocationGroups();
              this.updateMapWithJobLocationGroupMarkers(this.searchResults.jobLocationGroups);
            },
            computeDistanceData(lat, lng) {
                console.log('computeDistanceData');
                this.updateJobsDistance(this.searchResults.jobs, lat, lng);

                this.searchResults.display = true;

                // create map now that containing div is visible


                // create map markers
                var marker = new google.maps.Marker({
                  position: this.mapOptions.center,
                  map: this.map,
                  label: this.searchTerm
                });

            },

//            updateJobsWithGeocoderData(origin) {
//                const jobLatLngStrs = [];
//                for (let job of this.searchResults.jobs) {
//                    jobLatLngStrs.push(job.prison_location.lat + ',' + job.prison_location.lng);
//                }
//                var service = new google.maps.DistanceMatrixService();
////                service.getDistanceMatrix(
////                    {
////                        origins: [origin],
////                        destinations: jobLatLngStrs,
////                        travelMode: google.maps.TravelMode.DRIVING,
////                        unitSystem: google.maps.UnitSystem.IMPERIAL,
////                        avoidHighways: false,
////                        avoidTolls: false
////                    }, this.handleDistanceMatrixData);
//                this.computeDistanceData(origin)
//            },

            processGeocoderResults(results, status) {
                console.log('processGeocoderResults');
                if (status == google.maps.GeocoderStatus.OK) {
                    console.log('processGeocoderResults status is ok');
                    const location = results[0].geometry.location;
                    const latLng = new google.maps.LatLng(location.lat(), location.lng());

                    // create map
                    this.mapOptions = Object.assign({}, this.mapOptions, {center: latLng});

                    //this.updateJobsWithGeocoderData(location.lat() + ',' + location.lng());
                    this.computeDistanceData(location.lat(), location.lng());
                } else {
                    alert('Geocode was not successful for the following reason: ' + status);
                }
            },

            search() {
                console.log('search', this.searchResults.searchTerm);
                new google.maps.Geocoder().geocode(
                    { 'address': this.searchResults.searchTerm},
                    this.processGeocoderResults
                );
            },

            showMapView() {
                this.searchResults.activeView = 0;
            },

            showListView() {
                this.searchResults.activeView = 1;
            },

            showPage(i) {
                this.searchResults.listView.activePage = i
            },

            showNextPage() {
                if (this.searchResults.listView.activePage < (this.numberOfResultPages - 1)) {
                    this.searchResults.listView.activePage++
                }
            },

            showPreviousPage() {
                if (this.searchResults.listView.activePage > 0) {
                    this.searchResults.listView.activePage--
                }
            }
        },

        mounted() {
            console.log('about to search ...');
            this.createMap();

            if (this.searchResults.searchTerm) {
                this.search();
            }

            const dist = this.calculateDistanceBetweenTwoLatLngPoints(0, 0, 0, 1);
            console.log('dist', dist);
        }
    }
</script>
