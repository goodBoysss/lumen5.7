<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\AdminActionLog;
use App\Models\AdminLoginLog;
use App\Models\App;
use App\Models\AppDataLog;
use App\Models\AppUser;
use App\Models\AppUserGiveGift;
use App\Models\AppUserLogin;
use App\Models\AppUserLoginSum;
use App\Models\AppUserRecharge;
use App\Models\AppUserRechargeSum;
use App\Models\AppUserRegister;
use App\Models\Channel;
use App\Models\ChannelDayStatistic;
use App\Models\ChannelDaySumStatistic;
use App\Models\ChannelLog;
use App\Models\ChannelParentsChangeLog;
use App\Models\ChannelUserActionLog;
use App\Models\ChannelMaterialRel;
use App\Models\ChannelUserLoginLog;
use App\Models\ExportDownloadLog;
use App\Models\ExportRecord;
use App\Models\File;
use App\Models\InvitationImageCode;
use App\Models\InvitationRegisterRecord;
use App\Models\InvitationRegisterSmsLog;
use App\Models\InvitationVisitLog;
use App\Models\Material;
use App\Models\MaterialImage;
use Illuminate\Support\ServiceProvider;

class ModelServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    //应用
    private function app()
    {

    }
}
