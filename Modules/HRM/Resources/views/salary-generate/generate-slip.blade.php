@php
    use Modules\HRM\Entities\SalaryGenerate;
    use Illuminate\Support\Facades\DB;
    use Modules\HRM\Entities\AllowanceDeductionManage;
@endphp
@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<style>
</style>
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title">
                    <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                </div>
                <div class="card-toolbar">
                    <!--begin::Button-->
                    <button type="button" class="btn btn-primary btn-sm mr-3" id="print-invoice"> <i class="fas fa-print"></i> Print</button>
                    
                    <a href="{{ route('purchase') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <div class="card-body" style="padding-bottom: 100px !important;">
                <div class="col-md-12 col-lg-12"  style="width: 100%;">
                    <div id="invoice">
                        <style>
                            body,html {
                                background: #fff !important;
                                -webkit-print-color-adjust: exact !important;
                            }

                            .invoice {
                                /* position: relative; */
                                background: #fff !important;
                                /* min-height: 680px; */
                            }

                            .invoice header {
                                padding: 10px 0;
                                margin-bottom: 20px;
                                border-bottom: 1px solid #036;
                            }

                            .invoice .company-details {
                                text-align: right
                            }

                            .invoice .company-details .name {
                                margin-top: 0;
                                margin-bottom: 0;
                            }

                            .invoice .contacts {
                                margin-bottom: 20px;
                            }

                            .invoice .invoice-to {
                                text-align: left;
                            }

                            .invoice .invoice-to .to {
                                margin-top: 0;
                                margin-bottom: 0;
                            }

                            .invoice .invoice-details {
                                text-align: right;
                            }

                            .invoice .invoice-details .invoice-id {
                                margin-top: 0;
                                color: #036;
                            }

                            .invoice main {
                                padding-bottom: 50px
                            }

                            .invoice main .thanks {
                                margin-top: -100px;
                                font-size: 2em;
                                margin-bottom: 50px;
                            }

                            .invoice main .notices {
                                padding-left: 6px;
                                border-left: 6px solid #036;
                            }

                            .invoice table {
                                width: 100%;
                                border-collapse: collapse;
                                border-spacing: 0;
                                margin-bottom: 20px;
                            }

                            .invoice table th {
                                background: #036;
                                color: #fff;
                                padding: 5px;
                                border-bottom: 1px solid #fff
                            }

                            .invoice table td {
                                padding: 5px;
                                border-bottom: 1px solid #fff
                            }

                            .invoice table th {
                                white-space: nowrap;
                            }

                            .invoice table td h3 {
                                margin: 0;
                                color: #036;
                            }

                            .invoice table .qty {
                                text-align: center;
                            }

                            .invoice table .price,
                            .invoice table .discount,
                            .invoice table .tax,
                            .invoice table .total {
                                text-align: right;
                            }

                            .invoice table .no {
                                color: #fff;
                                background: #036
                            }

                            .invoice table .total {
                                background: #036;
                                color: #fff
                            }

                            .invoice table tbody tr:last-child td {
                                border: none
                            }

                            .invoice table tfoot td {
                                background: 0 0;
                                border-bottom: none;
                                white-space: nowrap;
                                text-align: right;
                                padding: 10px 20px;
                                border-top: 1px solid #aaa;
                                font-weight: bold;
                            }

                            .invoice table tfoot tr:first-child td {
                                border-top: none
                            }

                            /* .invoice table tfoot tr:last-child td {
                                color: #036;
                                border-top: 1px solid #036
                            } */

                            .invoice table tfoot tr td:first-child {
                                border: none
                            }

                            .invoice footer {
                                width: 100%;
                                text-align: center;
                                color: #777;
                                border-top: 1px solid #aaa;
                                padding: 8px 0
                            }

                            .invoice a {
                                content: none !important;
                                text-decoration: none !important;
                                color: #036 !important;
                            }

                            .page-header,
                            .page-header-space {
                                height: 100px;
                            }

                            .page-footer,
                            .page-footer-space {
                                height: 20px;

                            }

                            .page-footer {
                                position: fixed;
                                bottom: 0;
                                width: 100%;
                                text-align: center;
                                color: #777;
                                border-top: 1px solid #aaa;
                                padding: 8px 0
                            }

                            .page-header {
                                position: fixed;
                                top: 0mm;
                                width: 100%;
                                border-bottom: 1px solid black;
                            }

                            .page {
                                page-break-after: always;
                            }
                            .dashed-border{
                                width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                            }

                            @media screen {
                                .no_screen {display: none;}
                                .no_print {display: block;}
                                thead {display: table-header-group;} 
                                tfoot {display: table-footer-group;}
                                button {display: none;}
                                body {margin: 0;}
                            }

                            @media print {

                                body,
                                html {
                                    /* background: #fff !important; */
                                    -webkit-print-color-adjust: exact !important;
                                    font-family: sans-serif;
                                    /* font-size: 12px !important; */
                                    margin-bottom: 100px !important;
                                }

                                .m-0 {
                                    margin: 0 !important;
                                }

                                h1,
                                h2,
                                h3,
                                h4,
                                h5,
                                h6 {
                                    margin: 0 !important;
                                }

                                .no_screen {
                                    display: block !important;
                                }

                                .no_print {
                                    display: none;
                                }

                                a {
                                    content: none !important;
                                    text-decoration: none !important;
                                    color: #036 !important;
                                }

                                .text-center {
                                    text-align: center !important;
                                }

                                .text-left {
                                    text-align: left !important;
                                }

                                .text-right {
                                    text-align: right !important;
                                }

                                .float-left {
                                    float: left !important;
                                }

                                .float-right {
                                    float: right !important;
                                }

                                .text-bold {
                                    font-weight: bold !important;
                                }

                                .invoice {
                                    /* font-size: 11px!important; */
                                    overflow: hidden !important;
                                    background: #fff !important;
                                    margin-bottom: 100px !important;
                                }

                                .invoice footer {
                                    position: absolute;
                                    bottom: 0;
                                    left: 0;
                                    /* page-break-after: always */
                                }

                                /* .invoice>div:last-child {
                                    page-break-before: always
                                } */
                                .hidden-print {
                                    display: none !important;
                                }
                                .dashed-border{
                                    width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                                }
                            }

                            @page {
                                /* size: auto; */
                                margin: 5mm 5mm;

                            }
                        </style>
                        <div class="invoice overflow-auto">
                            <div>
                                <table>
                                    <tr>
                                        <td class="text-center">
                                            <h2 class="name m-0" style="text-transform: uppercase;"><b>{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</b></h2>
                                            @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>Contact No.: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>Email: </b>{{ config('settings.email') }}@endif</p>@endif
                                            @if(config('settings.address'))<p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>@endif
                                            <p style="font-weight: normal;margin:0;"><b>Date: </b>{{ date('d-M-Y') }}</p>
                                        </td>
                                    </tr>
                                </table>
                                <div style="width: 100%;height:3px;border-top:1px solid #036;border-bottom:1px solid #036;"></div>
                                <div class="row" style="border: 1px solid #ddd;padding: 26px 9px">
							        <div class="col-md-6">
                                        <table>
                                            <tr>
                                                <td width="50%"><b>Name :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php $employee = SalaryGenerate::getAnyRowInfos('employees','id',$payslip->employee_id);echo $employee->name?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%"><b>Department :</b></td>
                                                <td width="50%" class="text-left">
                                                    {{SalaryGenerate::getAnyRowInfos('departments','id',$payslip->department_id)->name}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%"><b>Designation :</b></td>
                                                <td width="50%" class="text-left">
                                                    {{SalaryGenerate::getAnyRowInfos('designations','id',$payslip->designation_id)->name}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%"><b>Joining Date :</b></td>
                                                <td width="50%" class="text-left">
                                                    {{$employee->joining_date}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%"><b>Basic Salary :</b></td>
                                                <td width="50%" class="text-left">
                                                    {{$payslip->basic_salary}}
                                                </td>
                                            </tr>
                                            <?php                                           

                                            $allallowance = DB::select(DB::raw("SELECT allowAmount.amount as amount,allowAmount.employee_id,allow.id,allow.name as aname,allow.short_name as asname,allow.type as atype FROM `allowance_deduction_manages` as allowAmount 
                                            JOIN `allowance_deductions` as allow ON allow.id=allowAmount.allowance_deduction_id WHERE allowAmount.employee_id='" . $payslip->employee_id . "' 
                                            group by allowAmount.allowance_deduction_id"));
                                            $allowanceArray = array();
                                            $deducteArray = array();
                                            $tallow=0;
                                            $tdeduct=0;
                                            foreach($allallowance as $allowance):
                                                if($allowance->atype == 1){
                                                    $allowanceAmount = $allowance->amount;
                                                    $allowanceArray[$allowance->aname] = $allowance->amount;
                                                }else if($allowance->atype == 2){
                                                    $deducteArray[$allowance->aname] = $allowance->amount;
                                                }
                                            endforeach; ?>   
                                            <?php foreach($allowanceArray  as $key => $allow):?>                                         
                                            <tr>
                                                <td width="50%"><b>{{$key}} :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php $tallow +=$allow; echo $allow;?>
                                                </td>
                                            </tr>
                                            <?php  endforeach;?>                                            
                                            <tr>
                                                <td width="50%"><b>Net salary :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php echo $netSalary = ($payslip->basic_salary+$tallow);?>
                                                </td>
                                            </tr>
                                            <?php foreach($deducteArray  as $key => $deduct):?>                                         
                                            <tr>
                                                <td width="50%"><b>{{$key}} :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php $tdeduct +=$deduct; echo $deduct;?>
                                                </td>
                                            </tr>
                                            <?php  endforeach;?>                                           
                                            <tr>
                                                <td width="50%"><b>Absent Amount :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php echo $payslip->absent_amount;?>
                                                </td>
                                            </tr>                                            
                                            <tr>
                                                <td width="50%"><b>Leave Amount :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php echo $payslip->leave_amount;?>
                                                </td>
                                            </tr>                                         
                                            <tr>
                                                <td width="50%"><b>Net salary to be paid :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php echo $netSalaryPay = ($netSalary-($tdeduct + $payslip->absent_amount+$payslip->leave_amount));?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
							        <div class="col-md-6">
                                        <table>
                                            <tr>
                                                <td width="50%"><b>No :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php echo $employee->id?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%"><b>Month :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php echo date('F Y',strtotime($payslip->salary_month))?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%"><b>Date :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php echo date('d-F-Y',strtotime($payslip->salary_month))?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%"><b>Number of working days :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php echo "26";?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%"><b>Number of day worked in the month :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php echo $present = (26-$payslip->absent);?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%"><b>Unjustified absence :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php echo $payslip->absent;?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%"><b>Per day salary :</b></td>
                                                <td width="50%" class="text-left">
                                                    <?php echo number_format(($payslip->basic_salary/$present),2);?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/jquery.printarea.js"></script>
<script>
$(document).ready(function () {
    //QR Code Print
    $(document).on('click','#print-invoice',function(){
        var mode = 'iframe'; // popup
        var close = mode == "popup";
        var options = {
            mode: mode,
            popClose: close
        };
        $("#invoice").printArea(options);
    });
});

</script>
@endpush