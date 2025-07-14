<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiAppManToolsController extends Controller
{
    private $conn_db;
    private $sql_server_conn_db;

    public function __construct()
    {
    }
    private function setYmSoftConnection()
    {
        // GRANT ALL PRIVILEGES ON *.* TO 'justusku'@'128.199.214.77' IDENTIFIED BY 'JUStusBerk4H123';
        config(['database.connections.ymsoft_connection' => [
            'driver' => 'mysql',
            //'host' => '128.199.214.77',
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'justusku_cms',
            'username' => 'justusku',
            'password' => 'JUStusBerk4H123',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]]);
        $this->conn_db = DB::connection('ymsoft_connection');
    }
    private function resetYmSoftConnection()
    {
        config(['database.connections.ymsoft_connection' => []]);
    }
    public function querysetYmSoft()
    {
        $this->setYmSoftConnection();
        DB::beginTransaction();
        try {
            $results = $this->conn_db->select('select * from some_table');
            //-------code-------
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        } finally {
            $this->resetYmSoftConnection();
        }
    }



    private function setSqlServerConnection()
    {
        config(['database.connections.sql_server_connection' => [
            'driver'   => 'sqlsrv',
            'host'     => 'ymserver1.ddns.net,1355',
            'database' => 'YMSoft Server',
            'username' => 'sa',
            'password' => 'yudit4ma',
            'charset'  => 'utf8',
            'prefix'   => '',
        ]]);
        $this->sql_server_conn_db = DB::connection('sql_server_connection');
    }

    private function resetSqlServerConnection()
    {
        config(['database.connections.sql_server_connection' => []]);
    }

    public function sqlServerQueryExample()
    {
        $this->setSqlServerConnection();
        DB::beginTransaction();
        try {
            $results = $this->sql_server_conn_db->select("SELECT TOP 1000 * FROM [dbo].[LapPenjualanYM]");
            dd($results);
            // -------code-------
            DB::commit();
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
        } finally {
            $this->resetSqlServerConnection();
        }
    }
    private function setODBCConnection()
    {
        config(['database.connections.odbc_connection' => [
            'driver' => 'odbc',
            'dsn' => 'DSN=DSNSQLServerYmSoft;Database=YMSoft Server',
            'host' => '',
            'database' => '',
            'username' => 'sa',
            'password' => 'yudit4ma',
            'charset' => 'utf8',
            'prefix' => '',
        ]]);
        $this->conn_db = DB::connection('odbc_connection');
    }
    public function queryODBC()
    {
        $this->setODBCConnection();
        DB::beginTransaction();
        try {
            $results = $this->conn_db->select("SELECT TOP 1000 * FROM [dbo].[LapPenjualanYM]");
            dd($results);
            DB::commit();
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
        } finally {
            config(['database.connections.odbc_connection' => []]);
        }
    }




    public function index(Request $request)
    {
        //return "/api/flutter_app_man_tools/load_chart";
        //phpinfo();
    }
    public function loadChart(Request $request)
    {
        /*
            currentReportType	:	0
            outlet	:	0
            theDate	:	15/10/2023
            startDate	:	08/10/2023
            endDate	:	15/10/2023
            rangeDate	:	08/10/2023 - 15/10/2023
            theYear	:	2023
        */
        //https://justusmember.co.id/api/flutter_app_man_tools/load_chart
        $this->setYmSoftConnection();
        DB::beginTransaction();
        try {
            $currentReportType = $request->currentReportType;
            $outlet = $request->outlet;
            $theDate = $request->theDate;
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $rangeDate = $request->rangeDate;
            $theYear = $request->theYear;
            /*
                '%m/%d/%Y %H:%i:%s'
                '%m/%d/%y %H:%i:%s'
                '%m/%d/%Y %h:%i:%s %p'
                '%d-%b-%y %h:%i:%s %p'
            */
            $whereQueryReportType0 = " WHERE 
                                    DATE(STR_TO_DATE(TglServer, '%m/%d/%Y %H:%i:%s')) = STR_TO_DATE('" . $theDate . "', '%d/%m/%Y') 
                                    or DATE(STR_TO_DATE(TglServer, '%m/%d/%y %H:%i:%s')) = STR_TO_DATE('" . $theDate . "', '%d/%m/%Y') 
                                    or DATE(STR_TO_DATE(TglServer, '%m/%d/%Y %h:%i:%s %p')) = STR_TO_DATE('" . $theDate . "', '%d/%m/%Y') 
                                    or DATE(STR_TO_DATE(TglServer, '%d-%b-%y %h:%i:%s %p')) = STR_TO_DATE('" . $theDate . "', '%d/%m/%Y') 
                                    ";
            $selColumnQueryReportType0 = "
                IFNULL(IFNULL(IFNULL(
                    HOUR(STR_TO_DATE(TglServer, '%m/%d/%Y %H:%i:%s')),
                    HOUR(STR_TO_DATE(TglServer, '%m/%d/%y %H:%i:%s'))),
                    HOUR(STR_TO_DATE(TglServer, '%m/%d/%Y %h:%i:%s %p'))),
                    HOUR(STR_TO_DATE(TglServer, '%d-%b-%y %h:%i:%s %p')))
                as TglServerHour, 
            " ;
            $queryReportType_0_all_outlet = "SELECT 
                'SH000' as KodeOutlet,
                    ".$selColumnQueryReportType0."
                SUM(GrandTotal) as TotalGrandTotal,
                SUM(Total) as TotalTotal,
                SUM(Disc) as TotalDisc,
                SUM(PPN) as TotalDPP,
                SUM(PPN) as TotalPPN,
                SUM(Service) as TotalService,
                SUM(CommFee) as TotalCommFee,
                SUM(Rounding) as TotalRounding,
                SUM(JumlahPax) as TotalPax
                FROM TblCafeOrderOutlet
                " . $whereQueryReportType0 . " 
                GROUP BY TglServerHour
                ORDER BY TglServerHour ASC;";
            $queryReportType_0_per_outlet = "SELECT 
                KodeOutlet,
                    ".$selColumnQueryReportType0."
                SUM(GrandTotal) as TotalGrandTotal,
                SUM(Total) as TotalTotal,
                SUM(Disc) as TotalDisc,
                SUM(PPN) as TotalDPP,
                SUM(PPN) as TotalPPN,
                SUM(Service) as TotalService,
                SUM(CommFee) as TotalCommFee,
                SUM(Rounding) as TotalRounding,
                SUM(JumlahPax) as TotalPax
                FROM TblCafeOrderOutlet
                 " . $whereQueryReportType0 . " 
                GROUP BY KodeOutlet, TglServerHour
                ORDER BY KodeOutlet, TglServerHour ASC;
                ";
            /*
                '%m/%d/%Y %H:%i:%s'
                '%m/%d/%y %H:%i:%s'
                '%m/%d/%Y %h:%i:%s %p'
                '%d-%b-%y %h:%i:%s %p'
            */
            $whereQueryReportType1 = " WHERE 
                        (DATE(STR_TO_DATE(TglServer, '%m/%d/%Y %H:%i:%s')) BETWEEN STR_TO_DATE('" . $startDate . "', '%d/%m/%Y') and STR_TO_DATE('" . $endDate . "', '%d/%m/%Y')) 
                        or (DATE(STR_TO_DATE(TglServer, '%m/%d/%y %H:%i:%s')) BETWEEN STR_TO_DATE('" . $startDate . "', '%d/%m/%Y') and STR_TO_DATE('" . $endDate . "', '%d/%m/%Y')) 
                        or (DATE(STR_TO_DATE(TglServer, '%m/%d/%Y %h:%i:%s %p')) BETWEEN STR_TO_DATE('" . $startDate . "', '%d/%m/%Y') and STR_TO_DATE('" . $endDate . "', '%d/%m/%Y')) 
                        or (DATE(STR_TO_DATE(TglServer, '%d-%b-%y %h:%i:%s %p')) BETWEEN STR_TO_DATE('" . $startDate . "', '%d/%m/%Y') and STR_TO_DATE('" . $endDate . "', '%d/%m/%Y')) 
                        ";
            $selColumnQueryReportType1 = "
                IFNULL(IFNULL(IFNULL(
                    DATE_FORMAT(STR_TO_DATE(TglServer, '%m/%d/%Y %H:%i:%s'), '%d/%m'), 
                    DATE_FORMAT(STR_TO_DATE(TglServer, '%m/%d/%y %H:%i:%s'), '%d/%m')), 
                    DATE_FORMAT(STR_TO_DATE(TglServer, '%m/%d/%Y %h:%i:%s %p'), '%d/%m')),
                    DATE_FORMAT(STR_TO_DATE(TglServer, '%d-%b-%y %h:%i:%s %p'), '%d/%m')) 
                as TglServerHour,
            ";
            $queryReportType_1_all_outlet = "SELECT 
                'SH000' as KodeOutlet,
                 ".$selColumnQueryReportType1."
                SUM(GrandTotal) as TotalGrandTotal,
                SUM(Total) as TotalTotal,
                SUM(Disc) as TotalDisc,
                SUM(PPN) as TotalDPP,
                SUM(PPN) as TotalPPN,
                SUM(Service) as TotalService,
                SUM(CommFee) as TotalCommFee,
                SUM(Rounding) as TotalRounding,
                SUM(JumlahPax) as TotalPax
                FROM TblCafeOrderOutlet
                 " . $whereQueryReportType1 . " 
                GROUP BY TglServerHour
                ORDER BY TglServerHour ASC;";
            $queryReportType_1_per_outlet = "SELECT 
                KodeOutlet,
                 ".$selColumnQueryReportType1."
                SUM(GrandTotal) as TotalGrandTotal,
                SUM(Total) as TotalTotal,
                SUM(Disc) as TotalDisc,
                SUM(PPN) as TotalDPP,
                SUM(PPN) as TotalPPN,
                SUM(Service) as TotalService,
                SUM(CommFee) as TotalCommFee,
                SUM(Rounding) as TotalRounding,
                SUM(JumlahPax) as TotalPax
                FROM TblCafeOrderOutlet
                 " . $whereQueryReportType1 . " 
                GROUP BY KodeOutlet, TglServerHour
                ORDER BY KodeOutlet, TglServerHour ASC;";

            /*
                '%m/%d/%Y %H:%i:%s'
                '%m/%d/%y %H:%i:%s'
                '%m/%d/%Y %h:%i:%s %p'
                '%d-%b-%y %h:%i:%s %p'
            */
            $whereQueryReportType2 = " WHERE 
                                    YEAR(STR_TO_DATE(TglServer, '%m/%d/%Y %H:%i:%s')) = '" . $theYear . "' 
                                    or YEAR(STR_TO_DATE(TglServer, '%m/%d/%y %H:%i:%s')) = '" . $theYear . "' 
                                    or YEAR(STR_TO_DATE(TglServer, '%m/%d/%Y %h:%i:%s %p')) = '" . $theYear . "' 
                                    or YEAR(STR_TO_DATE(TglServer, '%d-%b-%y %h:%i:%s %p')) = '" . $theYear . "' 
            ";
            $selColumnQueryReportType2 = "
                IFNULL(IFNULL(IFNULL( 
                    DATE_FORMAT(STR_TO_DATE(TglServer, '%m/%d/%Y %H:%i:%s'), '%M'), 
                    DATE_FORMAT(STR_TO_DATE(TglServer, '%m/%d/%y %H:%i:%s'), '%M')), 
                    DATE_FORMAT(STR_TO_DATE(TglServer, '%m/%d/%Y %h:%i:%s %p'), '%M')),
                    DATE_FORMAT(STR_TO_DATE(TglServer, '%d-%b-%y %h:%i:%s %p'), '%M')) 
                as TglServerHour, 
            ";
            $queryReportType_2_all_outlet = "SELECT 
                'SH000' as KodeOutlet,
                  ".$selColumnQueryReportType2."
                SUM(GrandTotal) as TotalGrandTotal,
                SUM(Total) as TotalTotal,
                SUM(Disc) as TotalDisc,
                SUM(PPN) as TotalDPP,
                SUM(PPN) as TotalPPN,
                SUM(Service) as TotalService,
                SUM(CommFee) as TotalCommFee,
                SUM(Rounding) as TotalRounding,
                SUM(JumlahPax) as TotalPax
                FROM TblCafeOrderOutlet
                 " . $whereQueryReportType2 . "
                GROUP BY TglServerHour
                ORDER BY TglServerHour ASC;";
            $queryReportType_2_per_outlet = "SELECT 
                KodeOutlet,
                ".$selColumnQueryReportType2."
                SUM(GrandTotal) as TotalGrandTotal,
                SUM(Total) as TotalTotal,
                SUM(Disc) as TotalDisc,
                SUM(PPN) as TotalDPP,
                SUM(PPN) as TotalPPN,
                SUM(Service) as TotalService,
                SUM(CommFee) as TotalCommFee,
                SUM(Rounding) as TotalRounding,
                SUM(JumlahPax) as TotalPax
                FROM TblCafeOrderOutlet
                 " . $whereQueryReportType2 . "
                GROUP BY KodeOutlet, TglServerHour
                ORDER BY KodeOutlet, TglServerHour ASC;";

            $result["data_request"] = $request->all();
            $result["data_list"] = null;
            $result["message"] = 'loadChart no data';
            $result["result"] = 0;

            if ($currentReportType == 0) {
                $data_list['all_outlet'] = $this->conn_db->select($queryReportType_0_all_outlet);
                $data_list['per_outlet'] = $this->conn_db->select($queryReportType_0_per_outlet);
                $result["data_list"] = $data_list;
                $result["query_all_outlet"] = $queryReportType_0_all_outlet;
                $result["query_per_outlet"] = $queryReportType_0_per_outlet;
                $result["message"] = 'loadChart today';
                $result["result"] = 1;
            } else if ($currentReportType == 1) {
                $data_list['all_outlet'] = $this->conn_db->select($queryReportType_1_all_outlet);
                $data_list['per_outlet'] = $this->conn_db->select($queryReportType_1_per_outlet);
                $result["data_list"] = $data_list;
                $result["query_all_outlet"] = $queryReportType_1_all_outlet;
                $result["query_per_outlet"] = $queryReportType_1_per_outlet;
                $result["message"] = 'loadChart daily';
                $result["result"] = 1;
            } else if ($currentReportType == 2) {
                $data_list['all_outlet'] = $this->conn_db->select($queryReportType_2_all_outlet);
                $data_list['per_outlet'] = $this->conn_db->select($queryReportType_2_per_outlet);
                $result["data_list"] = $data_list;
                $result["query_all_outlet"] = $queryReportType_2_all_outlet;
                $result["query_per_outlet"] = $queryReportType_2_per_outlet;
                $result["message"] = 'loadChart monthly';
                $result["result"] = 1;
            }
            return json_encode($result);
            DB::commit();
        } catch (\Exception $e) {
            echo ($e);
            DB::rollBack();
        } finally {
            $this->resetYmSoftConnection();
        }
    }
    public function loadOutletTransaction(Request $request)
    {
        //https://justusmember.co.id/api/flutter_app_man_tools/load_outlet_transaction
        $this->setYmSoftConnection();
        DB::beginTransaction();
        try {
            //---code---
            $kodeOutlet = $request->outlet;
            $theDate = $request->theDate;

            $queryReport = "SELECT IDCO, Nomor, Tanggal, `Table`, `User`, Waiters, GrandTotal, JumlahPax, OrderType, JenisPembayaran, TglServer as TglServer, KodeOutlet FROM TblCafeOrderOutlet
            WHERE
            KodeOutlet = '" . $kodeOutlet . "'
            AND DATE(STR_TO_DATE(TglServer, '%m/%d/%Y %h:%i:%s %p')) = STR_TO_DATE('" . $theDate . "', '%d/%m/%Y')
            order by TglServer DESC
            ;";

            $result["data_request"] = $request->all();
            $result["data_list"] = null;
            $result["message"] = 'loadOutletTransaction no data';
            $result["result"] = 0;

            $data_list['detail_outlet'] = $this->conn_db->select($queryReport);
            $result["data_list"] = $data_list;
            $result["message"] = 'loadOutletTransaction data';
            $result["result"] = 1;

            return json_encode($result);
            DB::commit();
        } catch (\Exception $e) {
            echo ($e);
            DB::rollBack();
        } finally {
            $this->resetYmSoftConnection();
        }
    }
    public function loadOutletTransactionDetail(Request $request)
    {
        //https://justusmember.co.id/api/flutter_app_man_tools/load_outlet_transaction_detail
        $this->setYmSoftConnection();
        DB::beginTransaction();
        try {
            //---code---
            $idco = $request->idco;
            $queryReport = "SELECT IDBarang, KodeBarang, NamaBarang, Qty, TipeMenu, Urutan, Harga  FROM TblCafeOrderDetailOutlet WHERE IDCO = '" . $idco . "' ORDER BY Urutan ASC;";

            $result["data_request"] = $request->all();
            $result["data_list"] = null;
            $result["message"] = 'loadOutletTransactionDetail no data';
            $result["result"] = 0;

            $data_list['detail_transaction'] = $this->conn_db->select($queryReport);
            $result["data_list"] = $data_list;
            $result["message"] = 'loadOutletTransactionDetail data';
            $result["result"] = 1;

            return json_encode($result);
            DB::commit();
        } catch (\Exception $e) {
            echo ($e);
            DB::rollBack();
        } finally {
            $this->resetYmSoftConnection();
        }
    }
    public function loadOutletMaster(Request $request)
    {
        //https://justusmember.co.id/api/flutter_app_man_tools/load_outlet_master
        $this->setYmSoftConnection();
        DB::beginTransaction();
        try {
            $query = "SELECT * FROM `tbldataoutlet` WHERE aktif = true ORDER BY nama_outlet ASC;";

            $result["data_request"] = $request->all();
            $result["data_list"] = null;
            $result["message"] = 'loadOutletMaster no data';
            $result["result"] = 0;

            $result["data_list"] = $this->conn_db->select($query);
            $result["message"] = 'loadOutletMaster data';
            $result["result"] = 1;

            return json_encode($result);
            DB::commit();
        } catch (\Exception $e) {
            echo ($e);
            DB::rollBack();
        } finally {
            $this->resetYmSoftConnection();
        }
    }
}
