<?php
namespace SPHERE\Application\Api\Billing\Accounting;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Billing\Bookkeeping\Export\Export;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

class AccountingDownload implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/AccountingDownload',
            __NAMESPACE__.'\AccountingDownload::downloadAccountingList'
        ));

    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    /**
     * @param null $GroupId
     *
     * @return bool|string
     */
    public function downloadAccountingList()
    {

        if(($ExcelContent = Export::useService()->getAccountingContentByGroup())){
            $fileLocation = Export::useService()->createAccountingExcelDownload($ExcelContent);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Fakturierung Beitragszahler ".date("d-m-Y").".xlsx")->__toString();
        }
        return 'Download nicht möglich';
    }
}