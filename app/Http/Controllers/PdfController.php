<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint; // افترض أن لديك Model اسمه Complaint
use PDF; // استدعاء facade الخاص بـ PDF

class PdfController extends Controller
{
    /**
     * يقوم بتوليد ملف PDF لجميع الشكاوى.
     *
     * @return \Illuminate\Http\Response
     */
    public function generateComplaintsPdf()
    {
        // 1. استخراج البيانات من قاعدة البيانات
        // يمكنك إضافة شروط هنا (مثل فلترة حسب الحالة، التاريخ، إلخ)
        $complaints = Complaint::all(); // أو أي استعلام تريده

        // 2. تحميل الـ View الخاص بالـ PDF وتمرير البيانات له
        $pdf = PDF::loadView('pdf.complaint_report', ['complaints' => $complaints]);

        // 3. إرجاع الـ PDF
        // الخيارات:
        // - download('filename.pdf'): يقوم بتنزيل الملف مباشرة
        // - stream('filename.pdf'): يعرض الملف في المتصفح (للعرض)
        // - output(): يعيد محتوى الملف كسلسلة نصية (إذا كنت تريد إرساله كـ JSON مع محتوى الملف)

        // الخيار الأكثر شيوعًا للـ API هو stream أو download
        return $pdf->stream('complaints_report_' . date('Y-m-d') . '.pdf');
        // أو لتنزيله مباشرة:
        // return $pdf->download('complaints_report_' . date('Y-m-d') . '.pdf');
    }

    /**
     * يقوم بتوليد ملف PDF لشكوى واحدة بناءً على ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generateSingleComplaintPdf($id)
    {
        $complaint = Complaint::findOrFail($id); // ابحث عن الشكوى أو ارفع خطأ 404

        // قم بإنشاء View لهذا الـ PDF أو عدل الـ View السابق ليدعم عرض شكوى واحدة
        // مثال: سأفترض وجود View جديد اسمه 'single_complaint_report.blade.php'
        $pdf = PDF::loadView('pdf.single_complaint_report', ['complaint' => $complaint]);

        return $pdf->stream('complaint_' . $id . '.pdf');
    }
}
