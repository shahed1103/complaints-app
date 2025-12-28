<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تنبيه أمني</title>
</head>
<body style="direction: rtl; font-family: Arial, sans-serif">

    <h2 style="color: #c0392b;">تنبيه أمني</h2>

    <p>
        مرحبًا <strong>{{ $user->name }}</strong>،
    </p>

    <p>
        تم رصد <strong>عدد متكرر من محاولات تسجيل الدخول الفاشلة</strong> على حسابك.
    </p>

    <p>
        حفاظًا على أمان حسابك، تم <strong>قفل الحساب مؤقتًا</strong>.
    </p>

    <p>
        إذا لم تكن أنت من قام بهذه المحاولات، ننصحك بما يلي:
    </p>

    <ul>
        <li>تغيير كلمة المرور فورًا</li>
        <li>عدم مشاركة بيانات الدخول مع أي جهة</li>
        <li>التواصل مع الدعم الفني عند الحاجة</li>
    </ul>

    <p>
        إذا كنت أنت، يمكنك المحاولة مرة أخرى بعد انتهاء مدة القفل.
    </p>

    <hr>

    <p style="font-size: 12px; color: #777;">
        هذا الإيميل تم إرساله تلقائيًا لأغراض أمنية، يرجى عدم الرد عليه.
    </p>

</body>
</html>
