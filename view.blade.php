@extends('layouts.app')

@section('content')
   <link rel="stylesheet" type="text/css" href="/data-tables/datatables.min.css"/>
  <style>
      .modal.and.carousel {
        position: fixed;
      }
      .modal-dialog {
        max-width: 900px !important;
      }
      .checked {
      color: orange;
    }

    .marker_detail_box {
      display: none;
      position: absolute;
      top: 40%;
      border: solid 1px #cccccc;
      background-color: #ffffff;
      left: 0;
      right: 0;
      width: 450px;
      margin-left: auto;
      margin-right: auto;
      padding: 6px;
    }

    .close_icon_map {
      position: absolute;
      right: 0px;
      top: -15px;
      background: #b5b5b5;
      color: #fff;
      font-size: 14px;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      z-index: 1;
      cursor: pointer;
    }

    .map_box_content { position: relative; }
    .map_box_content > div { float: left; }

    .marker_detail_box .map_view .btn:hover,
    .marker_detail_box .map_view .btn:active,
    .marker_detail_box .map_view .btn:focus{ color: #fff; }

  </style>

  <div class="success_message">
    @if (Session::has('message'))
      <div class='alert alert-success'><strong>Success!</strong>{!! session('message') !!}</div>
    @endif
  </div>

  <div class="bgc-white bd bdrs-3 p-20">
    <h4 class="c-grey-900 mB-20">Hotel Recommendation Map</h4>

    <form action="{{ route('recommendation-map.index') }}" method="GET" id="form">
      <div class="form-row">
        {{-- Country --}}
        <div class="form-group col-md-2">
          <label for="country">Country</label>

          <select name="country" id="country" class="form-control">
            <option value="">Select Country</option>

            @foreach ($activecountries as $country)
              <option
                value="{{ $country->iso1_code }}"
                @if ($country->iso1_code == $countryCode)
                  selected
                @endif
              >
                {{ $country->name . ', ' . $country->country_code }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Regions --}}
        <div class="form-group col-md-2">
          <label for="active_regions">Region</label>
          <select name="region" id="region_select" class="form-control">
            <option value="">Select Region</option>
              @if(!empty($regionsList))
                @foreach ($regionsList as $regionLists)
                  <option
                    value="{{ $regionLists->code }}"
                    @if ($regionLists->code == $regionCode)
                      selected
                    @endif >
                    {{ $regionLists->name }}
                  </option>
                @endforeach
              @endif
          </select>
        </div>

        {{-- Destination --}}
        <div class="form-group col-md-2">
          <label for="destination_select">Destination</label>

          <select name="destination" id="destination_select" class="form-control">
            <option value="">Select Destination</option>
            @if(!empty($activeDestinations))
             @foreach ($activeDestinations as $destination)
                <option
                  value="{{ $destination->active_destination_id }}"
                  @if ($destination->active_destination_id == $active_destination_id)
                    selected
                  @endif
                >
                  @php
                    $destinationName = empty($destination->destination_alias)
                      ? $destination->name
                      : $destination->destination_alias;
                  @endphp

                  {{ $destinationName . ', ' . strtoupper($destination->country) }}
                </option>
              @endforeach
            @endif
          </select>
        </div>

        {{-- Group Destination --}}
        <div class="form-group col-md-2">
          <label for="child_destination_select">Group Destinations</label>

          <select name="child_destination" id="child_destination_select" class="form-control">
            <option value="">Select Group Destination</option>
            @if(!empty($groupChildDestinations))
             @foreach ($groupChildDestinations as $destination)
                <option
                  value="{{ $destination->code }}"
                  @if ($destination->code == request()->get('child_destination'))
                    selected
                  @endif
                >
                  {{ $destination->name . ', ' . strtoupper($destination->country) }}
                </option>
              @endforeach
            @endif
          </select>
        </div>

        {{-- Child Destination --}}
        <div class="form-group col-md-2">
          <label for="hp_child_destination_select">Child Destinations</label>

          <select name="hp_child_destination" id="hp_child_destination_select" class="form-control">
            <option value="">Select Child Destination</option>
            @if(!empty($hpChildDestinations))
             @foreach ($hpChildDestinations as $destination)
                <option
                  value="{{ $destination->code }}"
                  @if ($destination->code == request()->get('hp_child_destination'))
                    selected
                  @endif
                >
                  {{ $destination->name . ', ' . strtoupper($destination->country) }}
                </option>
              @endforeach
            @endif
          </select>
        </div>

        {{-- Status --}}
        <div class="form-group col-md-2">

        </div>

        {{-- Status --}}
        <div class="form-group col-md-2">
          <label for="input_status">Ratings</label>
          <select class="destination-select" style="width:90%;" multiple="multiple" name="ratings[]" id="ratings">
            {{-- <option value="">select Ratings</option> --}}
            @for ($i = 1; $i <= 5; $i++)
              @php $selected=""; @endphp
                @if (!empty(request()->ratings))
                    @foreach (request()->ratings as $item)
                      @if ($item == $i)
                        @php
                            $selected = "selected";
                        @endphp
                      @endif
                    @endforeach
                @endif
                <option value="{{ $i }}" {{ $selected }}>{{ $i." star" }}</option>
            @endfor
          </select>
        </div>

        <div class="form-group col-md-4">
            <label for="hotel_code">Hotel</label>
            <select style="width:100%" id="hotel" class="form-control hotel-select select2" name="hotel_code" data-url="{{ route('hotel_recommendation.hotel.search') }}" data-placeholder="Search Hotel">
              @if (! empty($tgxHotelRequest))
                  <option value="{{ $tgxHotelRequest->hotel_code }}" selected>{{ $tgxHotelRequest->hotel_name }}</option>
              @endif

            </select>
        </div>

         <div class="custom_dates col-md-4" id="custom" >
          <div class="form-row">
            <div class="form-group col-md-6">
            <label for="from">Checkin Date: </label>
            <input type="text" id="modalFromDate" value="{{ request()->checkin_date }}" placeholder="select from date" name="checkin_date" autocomplete="off" class="form-control" >
          </div>
          <div class="form-group col-md-6">
            <label for="to">Checkout Date: </label>
            <input type="text" id="modalToDate" placeholder="select to date" value="{{ request()->checkout_date }}" autocomplete="off"  name="checkout_date" class="form-control">
          </div>
          </div>
        </div>

        <div class="form-group col-md-2">
          <label for="">&nbsp;</label>
          <br>

          <button type="submit" class="btn btn-primary">Filter</button>

          <a href="{{ route('recommendation.index') }}" class="btn btn-danger" id="filter_reset_btn">Reset</a>
        </div>
      </div>
    </form>

    <form  method="POST" id="recommendform">
      <input type="hidden" name="destination" id="destination" value="{{ $active_destination_id }}">
      <input type="hidden" name="country" value="{{ $countryCode }}">

      @csrf

      <div class="form-row">
        <div  class="col-md-2">
          Total Hotel:
          @if(!empty($hphotels))
            {{ count($hphotels) }}
          @else
            0
          @endif
        </div>

        <div class="col-md-2">
          @if(!empty($hphotels))
            @if (!empty($available_hotel_ids))
               <b style="color:green">Recommended Hotel: {{ count($hp_found_recommended) }}</b>
             @else
                  Recommended Hotel: {{ count($recommendation) }}
             @endif
          @else
            Recommended Hotel: 0
          @endif
        </div>

         <div  class="col-md-2">
          <b style="color:green">
            @if (! empty($available_hotel_ids))
              Un-Recommended Hotel:
              @if(!empty($available_hotel_ids))
                {{ count($available_hotel_ids) - count($hp_found_recommended) }}
              @endif
            @endif
          </b>
        </div>

        <div  class="col-md-2">
          <b style="color:green">
            @if (! empty($available_hotel_ids))
              Total Available Hotel:
              @if(! empty($available_hotel_ids))
                {{ count($available_hotel_ids) }}
              @endif
            @endif
          </b>
        </div>        
      </div>
      <br><br>
      {{--  {{ $hphotels->links() }}  --}}

    </form>
    @if(!empty($hphotels))
    <div style="position: relative; width: 100%; heightL 700px;">
        <div id="map" style="height: 700px; width:100%;" ></div>
        <div class="marker_detail_box">
          <div class="row">
              <div class="map_box_content">
                  <div class="close_icon_map"><i class="fa fa-times" aria-hidden="true"></i></div>
                  <div class="col-lg-5 col-sm-5 col-md-5 col-xs-12">
                      <img src="" id="popupImage" alt="" style="width:100%;">
                  </div>
                  <div class="col-lg-7 col-sm-7 col-md-7 col-xs-12">
                      <div class="row">
                          <div class="map_box_description">
                              <a href="" id="popupURL" target="_blank">
                                  <h5 id="popupTitle" title="Hotel Suites Gaby"></h5>
                              </a>
                              <span id="popupStars"></span>
                              <h6 id="popupPrice"></h6>
                          </div>
                      </div>
                      <div class="row">
                          <div class="map_view">
                              <a href="" id="popupURL_btn" class="unrecommend btn btn-primary" >Unrecommend</a>
                          </div>
                        </div>
                      </div>
                  </div>
              </div>
          </div>
        </div>
      </div>
      @else 
        @isset($active_destination_id )
        <div>No Hotels Found</div>
        @endisset
      @endif
    @php 
        $mapHotels = [];

       @endphp

      @foreach ($hphotels as $hotel)  
        @php 
        //var_dump($hotel->categoryCode['rating']);
        if (!is_null($hotel->recommended_id)){
          
          $image = $hotel->medias['value'][0]['url'];
          if (is_null($image)){
            $image = '/images/no-image-tgx-unsold.jpg';
          }

          $mapHotels[] = array(
              
              str_replace("'", " ",$hotel->hotel_name),
              $hotel->location['coordinates']['latitude'],
              $hotel->location['coordinates']['longitude'],
              array(
                  'name' =>  str_replace("'", " ",$hotel->hotel_name),
                  'stars' => $hotel->categoryCode['rating'],
                  'image' => $image,
                  'code' => $hotel->hotel_code,
                  'isRecommended' => true
              )
          );
        }
        
       

        @endphp          
      @endforeach

    <div class="row justify-content-center">
    </div>

     {{-- image modal --}}
   <div class="modal fade and carousel slide" id="lightbox">
    <div class="modal-dialog">
      <div class="modal-content">
        <div>
         <button type="button" class="close" data-dismiss="modal" style="font-size: 40px;position: absolute;right: 10px; z-index: 1;">&times;</button>
        </div>
        <div class="modal-body" id="">
          <div class="carousel slide" data-ride="carousel" id="carouselExampleIndicators">
              <p style="text-align: center"> Please wait images are loading.... </p>
          </div>
        </div><!-- /.modal-body -->
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
   </div><!-- /.modal -->

  </div>
  <?php


  ?>
  <script type="text/javascript" src="/data-tables/datatables.min.js"></script>
  <script src="/js/recommend_hotel.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
  <script>
    var zoneFetchUrl = "{{ route('tgx-fetch.region') }}";
  </script>
  <link rel="stylesheet" href="/css/multiselect.css">
  <script src="/js/multiselect.js"></script>

  <script>
   var markers = [];
    $(document).ready(function () {
     
      // Rating multiselect
      $('#ratings').multipleSelect({
          filter: true
      });

      // Hotel select
      var select2_elem = $('.hotel-select');

      var select2 = select2_elem.select2({
        minimumInputLength: 3,
        width: 'style',
        dropdownAutoWidth: true,
        delay: 250,
        allowClear: true,
        ajax: {
          url: function () {
            return $(this).data('url');
          },
          data: function (params) {
            var data = { search: params.term,
              parent_destination : $("#destination_select").val()
            };
            return data;
          },
          processResults: function (data) {
            return {
              results: data
            }
          }
        }
      });

      // Checkin - Checkout Date
      var dateFormat = "mm/dd/yy",
          from = $( "#modalFromDate" )
            .datepicker({
              defaultDate: "+1w",
              changeMonth: true,
              numberOfMonths: 2
            })
            .on( "change", function() {
              to.datepicker( "option", "minDate", getDate( this ) );
            }),
          to = $( "#modalToDate" ).datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 2
          })
          .on( "change", function() {
            from.datepicker( "option", "maxDate", getDate( this ) );
          });

        function getDate( element ) {
          var date;
          try {
            date = $.datepicker.parseDate( dateFormat, element.value );
          } catch( error ) {
            date = null;
          }
          return date;
        }


      $(".hotelimage").click(function(){
         var hotelcode = $(this).data('hotelcode');
          $("#carouselExampleIndicators").html();
          $("#carouselExampleIndicators").html('<p style="text-align: center"> Please wait images are loading.... </p>');
          $('#lightbox').show();
          $('#lightbox').appendTo("body")
          $.ajax({
              url: 'hotel_recommendation/hotelimagepreview?hotelcode='+hotelcode,
              type: "GET",
              contentType: false,
              cache: false,
              success: function (res) {
                if(res.status){
                     $("#carouselExampleIndicators").html(res.html);

                 }
              }
          });
      });

      // Recommend button
      $('.recommed').click(function(e){

        e.preventDefault();

        var id = $(this).data("id");
        var destination = $("#destination_select").val();
        //var btnText = $(this).data("name");
        var str =  $("#bl"+id).text();
        var btnText = str.replace(/\s+/g, '');
        var url = "";
        if(btnText == "MakeUn-Recommend"){
             url = "{{url('')}}/hotel_recommendation_delete/" + id + "/" + destination;
        }else{
             url = "{{url('')}}/hotel_recommendation_store/" + id + "/" + destination;
        }

        if (destination == "" || destination == null || destination == undefined) {
          alert("Please select destination first");
          return false;
        } else {

          $.ajax({
            url: url,
            method: "GET",
            success: function (resp) {
                if (resp.status == true) {
                    if(btnText == "MakeUn-Recommend"){
                        //$("#bl"+id).attr('data-name', 'MakeRecommend');
                        $("#bl"+id).text("Make Recommend");
                        $("#bl"+id).removeClass('btn-success');
                        $("#bl"+id).addClass('btn-primary');
                    }
                    else {
                        // $("#bl"+id).attr('data-name', 'MakeUn-Recommend');
                         $("#bl"+id).text("Make Un-Recommend");
                         $("#bl"+id).removeClass('btn-primary');
                         $("#bl"+id).addClass('btn-success');
                    }
                }
            }
          });
        }
      });

      // In-Active Button
      $('.inactive').click(function(e){

        e.preventDefault();

        var id = $(this).data("id");
        var destination = $("#destination_select").val();
        //var btnText = $(this).data("name");
        var str =  $("#in"+id).text();
        var btnText = str.replace(/\s+/g, '');
        var url = "";
        if(btnText == "MakeActive"){
             url = "{{url('')}}/hotel_inactive_delete/" + id + "/" + destination;
        }else{
             url = "{{url('')}}/hotel_inactive_store/" + id + "/" + destination;
        }

        if (destination == "" || destination == null || destination == undefined) {
          alert("Please select destination first");
          return false;
        } else {

          $.ajax({
            url: url,
            method: "GET",
            success: function (resp) {
                if (resp.status == true) {
                    if(btnText == "MakeActive"){
                        $("#in"+id).text("Make In-Active");
                        $("#in"+id).removeClass('btn-danger');
                        $("#in"+id).addClass('btn-info');
                    }
                    else {
                         $("#in"+id).text("Make Active");
                         $("#in"+id).removeClass('btn-info');
                         $("#in"+id).addClass('btn-danger');
                    }
                }
            }
          });
        }
      });


      $('.hotel-rating-select').change(function (event) {

        var rating = $(this).val();
        var code = $(this).data("code");
        var me = $(this);
        event.preventDefault();

        $.ajax({
          url: "{{URL('hotel_rating/edit')}}",
          method: "post",
          data: {
            hotelrating: rating,
            code : code,
            _token : "{{ csrf_token() }}"
          },
          dataType: "json",
          beforeSend: function() {
              $(".hr"+code).text('updating...');
          },
          success: function (resp) {
            //alert("successfully updated..");
            setTimeout(function() {
               $(".hr"+code).text('');
            }, 2000);
          },
          error: function(result){
            setTimeout(function() {
                $(".hr"+code).text('error');
              }, 2000);
            }
          });

      });

      $('.rating-select').change(function (event) {

          var rating = $(this).val();
          var code = $(this).data("code");
          var me = $(this);
          event.preventDefault();

          $.ajax({
            url: "{{URL('hotel_tripratingsdata/edit')}}",
            method: "post",
            data: {
              rating: rating,
              code : code,
              _token : "{{ csrf_token() }}"
            },
            dataType: "json",
            beforeSend: function() {
                $(".tr"+code).text('updating...');
            },
            success: function (resp) {
            setTimeout(function() {
                $(".tr"+code).text('');
              }, 2000);
            },
            error: function(result){
              setTimeout(function() {
                $(".tr"+code).text('error');
              }, 2000);
            }
          });
      });


      $('#example').DataTable( {
        responsive: true,
        pageLength: 100,
        "aLengthMenu": [[-1,100, 250, 500], ["All",100, 250, 500]],
        "iDisplayLength": 100
      });

      $('#form').validate({ // initialize the plugin
        rules: {
          country: {
            required: true
          },
        }
      });

      $("#selectAll").click(function(){
        $("input[type=checkbox]").prop('checked', $(this).prop('checked'));
      });
    });

    var available_hotel_ids = '{{ count($available_hotel_ids) }}';
    var map;
    var places = JSON.parse('<?php echo json_encode($mapHotels); ?>');

    

    function initMap() {
        var bounds = new google.maps.LatLngBounds();
        var map = new google.maps.Map(document.getElementById('map'), {
            zoomControl: true,
            scaleControl: true,
            center: {
                lat: places[0][1],
                lng: places[0][2]
            }
        });

        for (let i = 0; i < places.length; i++) {
            loc = new google.maps.LatLng(places[i][1], places[i][2]);
            bounds.extend(loc);
            setMarkers(loc, map, places[i]);
        }

        map.fitBounds(bounds);
        map.panToBounds(bounds);
        if(available_hotel_ids == 1) {
            $(window).load(function() {
                map.setZoom(5);
            });
        }

        map.addListener('click', function() {
          $(".marker_detail_box").hide();
        });
    }

    function setMarkers(location, map, params) {

      // Adds markers to the map.
      var image = {
          url: '/images/map_pin_blue.png',
          // This marker is 20 pixels wide by 32 pixels high.
          size: new google.maps.Size(30, 40),
          // The origin for this image is (0, 0).
          origin: new google.maps.Point(0, 0),
          // The anchor for this image is the base of the flagpole at (0, 32).
          anchor: new google.maps.Point(15, 32)
      };
      // Origins, anchor positions and coordinates of the marker increase in the X
      // direction to the right and in the Y direction down.
      var shape = {
          coords: [1, 1, 1, 20, 18, 20, 18, 1],
          type: 'poly'
      };
      var marker = new google.maps.Marker({
          position: location,
          map: map,
          icon: image,
          shape: shape,
          title: params[0],
          status: "active",
          extraParams: params[3],
          id: params[3]['code']
      });
      marker.addListener('click', function() {
          viewDetails(this);
      });
      markers.push(marker);
    }

    function viewDetails(marker) {
      document.getElementById("popupTitle").innerHTML = marker.extraParams.name;
      document.getElementById("popupImage").setAttribute("src", marker.extraParams.image);
      document.getElementById("popupImage").setAttribute('alt', marker.extraParams.name);
      // Show star ratings
      var ratings = '';
      for (let i = 0; i < 5; i++) {
        if (i < marker.extraParams.stars) {
          ratings += '<span style="color:#febb01;"><i class="fa fa-star" aria-hidden="true"></i></span>';
          } else {
            ratings += '<span class="grey_star"><i class="fa fa-star" aria-hidden="true"></i></span>';
          }
        }
        document.getElementById("popupStars").innerHTML = ratings;
        if(marker.extraParams['isRecommended'] == true) {
          document.getElementById("popupURL_btn").classList.remove("btn-success");
          document.getElementById("popupURL_btn").classList.add("btn-primary");
          document.getElementById("popupURL_btn").innerText = 'Unrecommend';
        }else {
          document.getElementById("popupURL_btn").classList.remove("btn-primary");
          document.getElementById("popupURL_btn").classList.add("btn-success");
          document.getElementById("popupURL_btn").innerText = 'Recommend';
        }
        document.getElementById("popupURL_btn").setAttribute("data-id", marker.extraParams.code);
        document.getElementById("popupURL_btn").setAttribute("data-marker", marker);
        $('.marker_detail_box').show();
     }

      $(".close_icon_map").click(function () {
        $(".marker_detail_box").hide();
      });

      // Un Recommend Button
      $('.unrecommend').click(function(e){
        var button = $(this);
        var str =  $(this).text();
        var btnText = str.replace(/\s+/g, '');
     
        var id = $(this).attr("data-id");
        var destination = $("#destination_select").val();

        if(btnText == "Unrecommend"){
             url = "/hotel_recommendation_map_delete/" + id + "/" + destination;
        }else{
             url = "/hotel_recommendation_map_store/" + id + "/" + destination;
        }

        //url = "/hotel_recommendation_delete/" + id + "/" + destination;
       e.preventDefault();

        var resultObject = markers.filter(function (location) { return location.id == id });
        //console.log(resultObject[0]);
        
        $.ajax({
            url: url,
            method: "GET",
            success: function (resp) {
              //console.log(resp);
              if (resp.status == true) {
               
                    if(btnText == "Unrecommend"){
                        //$("#bl"+id).attr('data-name', 'MakeRecommend');
                        button.text("Recommend");
                        button.removeClass('btn-primary');
                        button.addClass('btn-success');
                        resultObject[0].setIcon('/images/map_pin_grey.png');
                        resultObject[0].extraParams['isRecommended'] = false;
                    }
                    else {
                        // $("#bl"+id).attr('data-name', 'MakeUn-Recommend');
                        button.text("Unrecommend");
                        button.removeClass('btn-success');
                        button.addClass('btn-primary');
                        resultObject[0].setIcon('/images/map_pin_blue.png');
                        resultObject[0].extraParams['isRecommended'] = true;
                    }
                    //console.log('success',resultObject[0].extraParams['isRecommended']);
                }
            }
        });
      });
  </script>
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDEMJLfTZceVNSxiipZk7qJuOXJ8eezp3k&callback=initMap"></script>
@endsection
