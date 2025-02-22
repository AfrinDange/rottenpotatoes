<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use DB;

class MovieController extends Controller
{
    //retrieves movies list to display on movies page
    public function getMovieCard() {
        $movies = Movie::all('title', 'imdbID', 'poster')
                            ->sortBy('title');
        foreach ($movies as $movie) {
            //encoded to dataURL format to add to the html tag
            $dataURI = (string) base64_encode($movie->poster);
            $movie->poster = $dataURI;
        }
        return view('movielist', ['movies' => $movies]);
    }

    //retrieves movie details along with cast
    public function getMovieDetails($imdbID) {
        $movie = DB::select('SELECT * 
                            FROM movies 
                            WHERE imdbID = ?', [$imdbID]);

        $cast = DB::select('SELECT imdbID, role, name, headshot
                            FROM moviescast 
                                INNER JOIN actor ON moviescast.castimdbID = actor.imdbID
                                WHERE movieimdbID = ?
                                ORDER BY name ASC', [$imdbID]);   

        //encoded to dataURL format to add to the html tag
        $dataURI = (string) base64_encode($movie[0]->poster);
        $movie[0]->poster = $dataURI;
        foreach ($cast as $tup) {
            //encoded to dataURL format to add to the html tag
            $dataURI = (string) base64_encode($tup->headshot);
            $tup->headshot = $dataURI;
        }

        //get reviews
        $reviews = DB::select('SELECT username, avatar, title, content, rating, userID, movieimdbID
                                FROM moviesreview, userdetails 
                                WHERE userdetails.id = moviesreview.userID AND movieimdbID = ?', [$imdbID]);
        return view('movie')
                    ->with('movie', $movie)
                    ->with('cast', $cast)
                    ->with('reviews', $reviews);
    }
}
