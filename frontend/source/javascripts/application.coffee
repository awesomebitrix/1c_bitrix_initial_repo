angular.module 'App', []

angular
  .module 'App'
  .controller 'SearchController', ['$scope', '$http', ($scope, $http) ->
    $scope.cities = {}
    $scope.cities_search =
      param: null
    $scope.cities_list = []
    $scope.cities_current = null


    $http.get '/ajax/cities_selector.php', {}
      .then (response) ->
        $scope.cities = response.data
        $scope.cities_current = response.data.current
        $scope.cities_search.list = _.toArray response.data.list
      , ->
        console.log 'error'

    $scope.continents = [
      {name: 'Россия'},
      {name: 'Европа'},
      {name: 'Азия'},
      {name: 'Северная Америка'},
      {name: 'Южная Америка'},
      {name: 'Африка'},
      {name: 'Австралия и Океания'}
    ]

    $scope.continents_current = 'Россия'
    $scope.countries_current = null

    $scope.cities_search_filter = (element) ->
      return element.name.match new RegExp "^#{$scope.cities_search.param}", "i"

  ]

angular.element(document).ready ->
  angular.bootstrap document, ['App']
