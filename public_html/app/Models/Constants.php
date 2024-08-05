<?php

namespace App\Models;

use Illuminate\Validation\Rules\Enum;

final class Constants
{
    const FAQsType = 0;
    const isDeletedFAQsType = 1;

    const invitedForRoom = 4;

    const pushNotification = 1;
    
    const is_verified = 2;
    const is_subscribe_verified = 3;

    const notificationTypeFollow = 1;
    const notificationTypeComment = 2;
    const notificationTypeLike = 3;
    const notificationTypeInviteRoom = 4;
    const notificationTypeAcceptInvitationRoom = 5;
    const notificationTypejoinRoom = 6;
    const notificationTypeDirectjoinRoom = 7;
    const notificationTypeAcceptRoomRequest = 8;

    const android = 0;
    const iOS = 1;


}
