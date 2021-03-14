@extends('layouts.app')

@section('content')
@php
    $files = file_get_contents('https://covid19.mathdro.id/api/countries/in/confirmed');
@endphp
<div class="container">
  
        <div class="row justify-content-md-center">
        {{-- map sta --}}
          <div class="col col-lg-6">
            <div class="row">
              <script src="http://d3js.org/d3.v3.min.js"></script>
    <script src="http://d3js.org/topojson.v1.min.js"></script>
    <script src="https://rawgit.com/Anujarya300/bubble_maps/master/data/geography-data/datamaps.none.js"></script>
    <div id="india" style="height: 600px; width: 900px;"></div>
    <script>
      const countryName = <?php echo $files; ?>;
        var bubble_map = new Datamap({
            element: document.getElementById('india'),
            scope: 'india',
            geographyConfig: {
                popupOnHover: true,
                highlightOnHover: true,
                borderColor: '#444',
                borderWidth: 0.5,
                dataUrl: 'https://rawgit.com/Anujarya300/bubble_maps/master/data/geography-data/india.topo.json',
                popupTemplate: function(geography, data) { //this function should just return a string
                  for(i in countryName){
                    if(geography.properties.name == countryName[i]['provinceState']) {
                      var confirmed = '<br> confirmed:' + '<strong>' + countryName[i]['confirmed'] + '</strong>';
                      var active = ' active:' + countryName[i]['active']+ ' deaths:' + countryName[i]['deaths'];
                      var recovered = ' recovered:' + countryName[i]['recovered'];
                      return '<div class="hoverinfo">' + geography.properties.name + confirmed + active  + recovered +'</div>';
                    }
                  }
                }
            },
            fills: {
                defaultFill: '#dddddd'
            },
            setProjection: function (element) {
                var projection = d3.geo.mercator()
                    .center([88.9629, 23.5937]) // always in [East Latitude, North Longitude]
                    .scale(1000);
                var path = d3.geo.path().projection(projection);
                return { path: path, projection: projection };
            }
        });

    </script>
            </div>
          </div>
         
          <div class="col col-lg-6">
            <div class="container">
    <div style="padding-top: 30px;">
      <form method="POST" action="/home">
        <div class="row">
          @csrf
          <div class="col col-lg-8">
            <select type="text" name="country" class="form-select" aria-label="Countries">
                @foreach ($data['countries'] as $country)
                <option>{{$country}}</option>
                @endforeach  
            </select>   
          </div>  
          <div class="col col-lg-4">
              <button type="submit" class="btn btn-primary">ADD</button>
          </div>   
        </div>
      </form>     
    </div>
    {{-- unsetting the countries since done with them and thwy will be hindrence moving on --}}
    @php
      unset($data['countries']);
    @endphp

    @foreach ($data as $key => $item)
      <div class="card">
      <div class="card-body">
        <h5 class="card-title">{{$key}}</h5>
        <div class="row">
          <div class="jumbotron">
            <div class="row">
              <div class="col-sm-3 d-flex">
                <div class="card card-body flex-fill">
                  <div class="under-title">{{ $item['active'] }} <br> Active </div>   
                </div>
              </div>
              <div class="col-sm-3 d-flex">
                <div class="card card-body flex-fill">
                    <div class="under-title"> {{ $item['recovered'] }} <br>Recovered </div>   
                </div>
              </div>
              <div class="col-sm-3 d-flex">
                <div class="card card-body flex-fill">
                  <div class="under-title"> {{ $item['deaths'] }}  <br>Deceased </div>   
                </div>
              </div>
              <div class="col-sm-3 d-flex">
                <div class="card card-body flex-fill">
                  <div class="under-title"> {{ $item['confirmed'] }} <br>Total </div>   
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    @endforeach

</div>
              
          </div>
        </div>
      </div>
@endsection
