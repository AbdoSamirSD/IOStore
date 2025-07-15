<?php

namespace App\Observers;

use App\Models\Banner;
use App\Models\User;
use App\Notifications\NewOfferNotification;
use Notification;

class BannerObserver
{
    /**
     * Handle the Banner "created" event.
     */
    public function created(Banner $banner): void
    {
        // Notification::send(null, new NewOfferNotification($banner));
        //  foreach (User::all() as $user) {
        //     $user->notify(new NewOfferNotification($banner));
        // }
    }

    /**
     * Handle the Banner "updated" event.
     */
    public function updated(Banner $banner): void {}

    /**
     * Handle the Banner "deleted" event.
     */
    public function deleted(Banner $banner): void
    {
        //
    }

    /**
     * Handle the Banner "restored" event.
     */
    public function restored(Banner $banner): void
    {
        //
    }

    /**
     * Handle the Banner "force deleted" event.
     */
    public function forceDeleted(Banner $banner): void
    {
        //
    }
}
