<?php

namespace App\Http\Controllers;

use App\Models\member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }
    public function get_date()
    {
        $today = "<input id='record_date' type='date' value='" . date("Y-m-d") . "' class='form-control' onchange='get_member()'>";
        return $today;
    }


    public function txt_output(Request $request)
    {
        $record_date = date("Y-m-d", strtotime($request->record_date));

        //指定此方法的response header的內容配置為"附件"
        header('Content-Disposition: attachment; filename="' . $record_date . '打卡紀錄.txt"'); //Content-Disposition: attachment=>網頁內容配置為附件,filename=>檔名設置

        $condition=[['record_date', '=',$record_date]];  //放SQL指令裡,where的相關指令/條件
        $table = DB::table('punch_records')
            ->whereNotNull("punch_in_time")
            ->where($condition);

        $table1 = DB::table('punch_records')
            ->whereNotNull("punch_out_time")
            ->where($condition)
            ->union($table)
            ->orderByRaw('user_id')
            ->get();

        $txt = "";

        foreach ($table1 as $row) {
            $user_id = $row->user_id;
            $punch_in_time = $row->punch_in_time;
            $punch_out_time = $row->punch_out_time;

            if ($punch_in_time != null) {
                $txt .= "$user_id";
                $txt .= "         ";
                $txt .= "01";
                $txt .= "$punch_in_time";
                $txt .= "A";
                $txt .= "\r\n";
            }

            if ($punch_out_time != null) {
                $txt .= "$user_id";
                $txt .= "         ";
                $txt .= "01";
                $txt .= $punch_out_time;
                $txt .= "B";
                $txt .= "\r\n";
            }
        }

        return $txt;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request == null) {
            return 'error';
        }

        $user_id = $request->user_id;
        $record_date = date("Y-m-d", strtotime($request->record_date));
        $status = $request->status;
        $select = $request->select;
        $remarks = $request->remarks;
        date_default_timezone_set('Asia/Taipei');   //調整時區為台北
        $now = date("Y-m-d H:i:s");

        $table = DB::table('punch_records')
            ->where([
                ['user_id',  $user_id],
                ['record_date',  $record_date]
            ])->first();

        $column = "";
        $record_id = $table;

        //取得員工簽到簽退表單
        if ($select == "get_member") {
            $punchtable = DB::table('punch_records')
                ->where('record_date', $record_date);
            $index = DB::table('members')
                ->leftJoinSub($punchtable, 'punch_records', function ($join) {
                    $join->on('members.user_id',  'punch_records.user_id');
                })
                ->whereNotNull('members.group_id')
                ->orderBy('members.user_id', 'asc')
                ->select('members.user_id', 'members.name', 'members.group_id', 'punch_records.remarks', 'punch_records.punch_in_time', 'punch_records.punch_out_time')
                ->get();

            $test = "<table class='table table-bordered'><thead>
                <tr>
                <th colspan=5><h3>第A組</h3></th>
                </tr>";
            $test1 = "<table class='table table-bordered'><thead>
                <tr>
                <th colspan=5><h3>第B組</h3></th>
                </tr>";
            $title = "<tr>
            <th style='min-width: 80px;'>員編</th>
            <th style='min-width: 80px;'>姓名</th>
            <th style='min-width: 120px;'>簽到</th>
            <th style='min-width: 120px;'>簽退</th>
            <th style='min-width: 120px;'>備註</th>
            </tr>
            </thead>";

            $test .= $title;
            $test1 .= $title;

            foreach ($index as $row) {
                $user_id = $row->user_id;
                $name = $row->name;
                $punch_in_time = $row->punch_in_time;
                $punch_out_time = $row->punch_out_time;
                $group_id = $row->group_id;
                $remarks = $row->remarks;
                $status_in = '1';
                $status_out = '2';
                $content = '<tbody><tr>
                <td class="align-middle">' . $user_id . '</td>
                <td class="align-middle">' . $name . '</td>
                <td class="align-middle">' . ($punch_in_time != "" ? $punch_in_time : '<button class="btn btn-success" onclick="punch(' . $user_id . ',' . $status_in . ')" >簽到</button>') . '
                </td>
                <td class="align-middle">' . ($punch_out_time != "" ? $punch_out_time : '<button class="btn btn-danger" onclick="punch(' . $user_id . ',' . $status_out . ')">簽退</button>') . '
                </td>
                <td class="align-middle">
                <input type="text" class="form-control" value="' . $remarks . '" onchange="set_remark(' . $user_id . ',this.value)">
                </td>
                </tr></tbody>';

                if ($group_id == 'A') {
                    $test .= $content;
                } elseif ($group_id == 'B') {
                    $test1 .= $content;;
                }
            }

            $test .= "</table>";
            $test1 .= "</table>";
            $div = '<table><tbody>
            <tr>
            <td align-top>' . $test . '</td>
            <td align-top>' . $test1 . '</td>
            </tr></tbody>
            </table>
            ';

            return $div;
        }

        //新增更新簽到簽退時間
        if ($select == "punch") {
            if ($user_id != "" && $status != "" && $record_date != "" && ($status == '1' || $status == '2')) {
                switch ($status) {
                    case '1':
                        $column = 'punch_in_time';
                        break;
                    case '2':
                        $column = 'punch_out_time';
                        break;
                }
            }
            if ($record_id == "") {
                DB::table('punch_records')->insert(['user_id' => $user_id, 'record_date' => $record_date, $column => $now]);
            } else {
                DB::table('punch_records')->upsert([
                    ['user_id' => $user_id, 'record_date' => $record_date, $column => $now]
                ], ['user_id', 'record_date'], [$column]);
            }
            return "OK";
        }

        //新增更新備註
        if ($select == "set_remark") {
            if ($record_id == "") {
                DB::table('punch_records')->insert(['user_id' => $user_id, 'record_date' => $record_date, 'remarks' => $remarks]);
            } else {
                DB::table('punch_records')->upsert([
                    ['user_id' => $user_id, 'record_date' => $record_date, 'remarks' => $remarks]
                ], ['user_id', 'record_date'], ['remarks']);
            }

            return "OK";
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\member  $member
     * @return \Illuminate\Http\Response
     */
    public function show(member $member)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\member  $member
     * @return \Illuminate\Http\Response
     */
    public function edit(member $member)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\member  $member
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, member $member)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\member  $member
     * @return \Illuminate\Http\Response
     */
    public function destroy(member $member)
    {
        //
    }
}
