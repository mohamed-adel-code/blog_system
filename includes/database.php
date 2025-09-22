<?php

// تضمين ملف الإعدادات
require_once 'config.php';

class Database
{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh; // Database Handler
    private $stmt; // Statement
    private $error;

    public function __construct()
    {
        // إعداد DSN (Data Source Name)
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT => true, // اتصال مستمر
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // عرض الأخطاء كاستثناءات
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // جلب البيانات كـ associative array
            PDO::ATTR_EMULATE_PREPARES => false, // تعطيل محاكاة الاستعلامات المعدة
        ];

        // إنشاء نسخة جديدة من PDO
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo 'فشل الاتصال: ' . $this->error;
            exit();
        }
    }

    // دالة لإعداد الاستعلام
    public function query($sql)
    {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // دالة لربط المتغيرات
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // دالة لتنفيذ الاستعلام
    public function execute()
    {
        return $this->stmt->execute();
    }

    // دالة لجلب كل النتائج
    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // دالة لجلب نتيجة واحدة
    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    // دالة لجلب عدد الصفوف المتأثرة
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    // دالة لجلب آخر ID تم إدخاله
    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }
}

// إنشاء كائن من الكلاس للوصول إلى قاعدة البيانات في جميع أنحاء المشروع
$db = new Database();
