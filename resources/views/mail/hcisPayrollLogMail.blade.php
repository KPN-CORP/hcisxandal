<!DOCTYPE html>
<html>

<head>
    <title>HCIS Payroll Log</title>
</head>

<body>
    <table style="border-collapse: collapse; width: 70%; margin-top: 8px; font-size: 10px;">
        <tr>
            <th colspan="7" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                <b>HCIS Payroll Log Detail :</b>
            </th>
        </tr>
        <tr style="font-weight: bold; background-color: #f5f5f5;">
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Status</td>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Detail</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Success</td>
            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                @if ($isOk)
                    @if ($okUrl)
                        <p>Url: {{ $okUrl }}</p>
                    @endif
                    @if ($okPullDate)
                        <p>Pull Date: {{ $okPullDate }}</p>
                    @endif
                    @if ($okTotal)
                        <p>Total: {{ $okTotal }}</p>
                    @endif
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Error</td>
            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                @if ($isErr)
                    @if ($errUrl)
                        <p>Url: {{ $errUrl }}</p>
                    @endif
                    @if ($errHttpStatus)
                        <p>Http Status: {{ $errHttpStatus }}</p>
                    @endif
                    @if ($errResponseBody)
                        <p>Response Body: {{ $errResponseBody }}</p>
                    @endif
                    @if ($errException)
                        <p>Exception: {{ $errException }}</p>
                    @endif
                @else
                    -
                @endif
            </td>
        </tr>
    </table>
</body>

</html>
