<?php

namespace App\Providers;

use App\Repositories\AdminActionLogRepository;
use App\Repositories\AdminLoginLogRepository;
use App\Repositories\AdminRepository;
use App\Repositories\AppDataLogRepository;
use App\Repositories\AppRepository;
use App\Repositories\AppUserGiveGiftRepository;
use App\Repositories\AppUserLoginRepository;
use App\Repositories\AppUserLoginSumRepository;
use App\Repositories\AppUserRechargeRepository;
use App\Repositories\AppUserRechargeSumRepository;
use App\Repositories\AppUserRegisterRepository;
use App\Repositories\AppUserRepository;
use App\Repositories\ChannelDaySumStatisticRepository;
use App\Repositories\ChannelLogRepository;
use App\Repositories\ChannelMaterialRelRepository;
use App\Repositories\ChannelParentsChangeLogRepository;
use App\Repositories\ChannelRepository;
use App\Repositories\ChannelDayStatisticRepository;
use App\Repositories\ChannelUserActionLogRepository;
use App\Repositories\ChannelUserLoginLogRepository;
use App\Repositories\ExportDownloadLogRepository;
use App\Repositories\ExportRecordRepository;
use App\Repositories\InvitationImageCodeRepository;
use App\Repositories\InvitationRegisterRecordRepository;
use App\Repositories\InvitationRegisterSmsLogRepository;
use App\Repositories\InvitationVisitLogRepository;
use App\Repositories\MaterialImageRepository;
use App\Repositories\MaterialRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {

    }
}
