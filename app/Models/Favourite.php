<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Favourite extends Model
{
    use HasFactory;

    protected $table = 'favourite';
    protected $fillable = ['user_id', 'favourite_against_id', 'type', 'date'];

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }

    public static function isFavourite($id, $type, $user_id)
    {
        $fav = Favourite::where([
            ['user_id', $user_id],
            ['favourite_against_id', $id],
            ['type', $type]
        ])->first();
        return $fav ? true : false;
    }

    public static function favouriteCount($id, $type)
    {
        $count = Favourite::where([
            ['favourite_against_id', $id],
            ['type', $type]
        ])->count();
        return $count;
    }

public static function getFavourites($user_id, $type) {
    $favourites = Favourite::where([
        ['user_id', $user_id],
        ['type', $type]
    ])->get();

    foreach ($favourites as $favourite) {
        if ($type == '1') {
            $product = Product::where('guid', $favourite->favourite_against_id)->where('is_deleted', 0)
                ->with(['brand', 'category', 'user', 'shop'])
                ->first();

            if ($product) {
                // Remove null attributes
                foreach ($product->getAttributes() as $key => $value) {
                    if (is_null($value)) {
                        unset($product->$key);
                    }
                }

                // Fetch seller data and feedback
                $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
                if ($sellerData) {
                    $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
                    $feedbackData = [];
                    foreach ($feedbacks as $feedback) {
                        $feedbackData[] = [
                            'id' => $feedback->id,
                            'user' => [
                                'image' => $feedback->user->media[0]->url ?? '',
                                'name' => $feedback->user->name . ' ' . $feedback->user->lastname,
                                'period' => $feedback->created_at->format("Y-m-d")
                            ],
                            'comments' => $feedback->comments,
                            'productname' => $feedback->product->name ?? ''
                        ];
                    }

                    $sellerDataCount = $feedbacks->count();
                    $positiveFeedbackCount = FeedBack::where('store_id', $product->shop_id)
                        ->where('is_positive', 1)
                        ->count();

                    $sellerData_ = [
                        'sellerName' => $sellerData->fullname,
                        'sellerImage' => env('APP_URL') . $sellerData->cover_image,
                        'positivefeedback' => $sellerDataCount > 0 ? number_format(($positiveFeedbackCount / $sellerDataCount) * 100, 2) : 0,
                        'feedback' => [
                            'count' => $sellerDataCount,
                            'feedbacks' => $feedbackData
                        ],
                        'is_favourite' => $sellerData->is_favourite,
                        'favourite_count' => $sellerData->favourite_count
                    ];
                } else {
                    $sellerData_ = [];
                }
                $product->seller = $sellerData_;
                $favourite->product = $product;
                $favourite->seller = (object)[];
            }
        } elseif ($type == '2') {
            $seller = SellerData::with('user')->where('guid', $favourite->favourite_against_id)->first();
            if ($seller) {
                $feedbackCount = $seller->feedback->count();
                $feedbackCount = $feedbackCount > 0 ? $feedbackCount : 1; // Avoid division by zero
                $positiveFeedbackCount = FeedBack::where('store_id', $seller->id)->where('is_positive', 1)->count();
                $seller->positive = number_format(($positiveFeedbackCount / $feedbackCount) * 100, 2);

                $favourite->product = (object)[];
                $favourite->seller = $seller;
            }
        }
    }

    return $favourites;
}

}
