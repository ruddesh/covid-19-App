<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public $countries_to_find = ['India'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function load()
    {
        $this->add_countries_to_global_variable_countries_to_find();
        $countries = $this->get_countries();
        $data = $this->api_call_foreach_country_required($this->countries_to_find);
        $data['countries'] = $countries;
        return view('home', ['data' => $data]);
    }

    public function store()
    {
        $countryName = request()->all();
        if ($this->country_already_added($countryName['country'])) {
            return redirect('/home');
        }
        $country = new Country();
        $country->user_id = auth()->id();
        $country->country = $countryName['country'];
        $country->save();
        return redirect('/home');
    }

    public function get_countries()
    {
        $countries = [];
        $jsonCountries = json_decode(Http::get('https://covid19.mathdro.id/api/countries'), true);
        foreach ($jsonCountries['countries'] as $key => $country) {
            array_push($countries, $country['name']);
        }
        return $countries;
    }

    public function api_call_foreach_country_required($countries_to_find)
    {
        $data = array();
        $jsonWorld = json_decode(Http::get('https://covid19.mathdro.id/api'), true);
        $activeWorld = $jsonWorld['confirmed']['value'] - $jsonWorld['recovered']['value'] - $jsonWorld['deaths']['value'];
        $data['world'] = ['active' => $activeWorld,
            'confirmed' => $jsonWorld['confirmed']['value'],
            'recovered' => $jsonWorld['recovered']['value'],
            'deaths' => $jsonWorld['deaths']['value']];

        foreach ($countries_to_find as $country) {
            $api = 'https://covid19.mathdro.id/api/countries/' . $country;
            $json_data = json_decode(Http::get($api), true);
            $active = $json_data['confirmed']['value'] - $json_data['recovered']['value'] - $json_data['deaths']['value'];
            $data[$country] = ['active' => $active,
                'confirmed' => $json_data['confirmed']['value'],
                'recovered' => $json_data['recovered']['value'],
                'deaths' => $json_data['deaths']['value'],
            ];
        }
        return $data;
    }

    public function country_already_added($country)
    {
        $data = Country::where('id', auth()->id());
        foreach ($data as $value) {
            if ($value['country'] == $country) {
                return true;
            }
        }
        return;
    }

    public function add_countries_to_global_variable_countries_to_find()
    {
        $data = Country::where('user_id', auth()->id())->get();
        foreach ($data as $value) {
            array_push($this->countries_to_find, $value['country']);
        }
        return;
    }
}
