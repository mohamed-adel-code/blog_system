-- MySQL dump 10.13  Distrib 8.4.6, for Linux (x86_64)
--
-- Host: localhost    Database: blog_system
-- ------------------------------------------------------
-- Server version	8.4.6-0ubuntu0.25.04.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'programming','programming',''),(7,'diverse','diverse',''),(8,'general','general','');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `author_name` varchar(100) DEFAULT NULL,
  `author_email` varchar(100) DEFAULT NULL,
  `comment` text NOT NULL,
  `status` enum('approved','pending','spam') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,1,1,'Mohamed Adel','mohamed.adel.code@gmail.com','🤍 🙂','approved','2025-09-20 11:49:04'),(2,1,2,'Ahmed Fadl','ahmed@gmail.com','🤍🙂 استمر','approved','2025-09-20 11:54:36'),(3,1,1,'Mohamed Adel','mohamed.adel.code@gmail.com','good','approved','2025-09-21 09:14:51'),(4,1,4,'Mohamed','mohamed@gmail.com','amazing','approved','2025-09-22 08:14:07');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('published','draft') DEFAULT 'draft',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,1,1,'Mastering MySQL Transactions & Isolation Levels','mastering-mysql-transactions-isolation-levels','في أي نظام بيتعامل مع بيانات حقيقية  =>  زي نظام بنكي ، متجر إلكتروني ، أو حتى موقع بيسجّل طلبات مستخدمين . \r\nدايمًا في احتمال إن أكتر من عملية بتحاول تعدّل نفس البيانات في نفس اللحظة.\r\nلو ما اتعاملناش مع ده بشكل سليم، البيانات ممكن تتلخبط، وده أخطر شيء ممكن يحصل في قاعدة بيانات.\r\nعلشان كده MySQL بيوفر مفهومين مهمين جدًا :\r\n\r\n🧩 أولًا: الـ Transactions — وحدة التنفيذ الذكية\r\nقبل ما نفهم المعاملات (Transactions)، لازم نعرف إن في قواعد البيانات في مبدأ قياسي اسمه خصائص ACID، ودي اللي بتحدد ازاي المعاملات بتشتغل:\r\nAtomicity (الذرّية): المعاملة يا تتم كلها أو تتلغي كلها.\r\n\r\n\r\nConsistency (الاتساق): بعد أي معاملة، البيانات لازم تفضل في حالة صحيحة ومنطقية.\r\n\r\n\r\nIsolation (العزل): المعاملات ما تأثرش على بعض أثناء التنفيذ المتوازي.\r\n\r\n\r\nDurability (الدوام): لو العملية خلصت واتأكدت، بياناتها تفضل محفوظة حتى لو حصل انقطاع كهرباء أو عطل مفاجئ.\r\n\r\n\r\nالمعاملات (Transactions) بتخلّي مجموعة أوامر SQL تتنفذ كأنها عملية واحدة غير قابلة للتجزئة.\r\nتخيل عملية تحويل فلوس من حساب لحساب:\r\nتسحب من حساب (أمر SQL)\r\n\r\n\r\nتودّي المبلغ لحساب تاني (أمر SQL تاني)\r\n\r\n\r\nلو أول خطوة نجحت والتانية فشلت، هيبقى في فلوس راحت في الهوا… \r\nعلشان كده MySQL بيخلي العمليتين يتنفذوا ككتلة واحدة:\r\nلو حصلت مشكلة في أي خطوة، كل اللي حصل بيرجع يتلغي كأن مفيش حاجة حصلت (عن طريق أمر ROLLBACK).\r\nولو تمّت كلها بنجاح، بتتأكد العملية وبتتحفّظ في قاعدة البيانات (عن طريق أمر COMMIT).\r\n\r\n\r\n\r\n⚡ ثانيًا: ليه بنحتاج Isolation Levels أصلًا؟\r\nالمعاملات بتحل مشكلة \" يا تنجح كلها يا تفشل كلها \"،\r\nبس لسه عندنا مشكلة تانية :\r\nإيه اللي بيحصل لو في أكتر من Transaction شغالة في نفس الوقت؟\r\nهنا ممكن تظهر مشاكل تزامن زي :\r\nDirty Read: تقرأ بيانات اتعدّلت بس لسه ما اتأكدتش (وممكن تتلغي بعدها )\r\n\r\n\r\nNon-Repeatable Read: تقرأ نفس الصف مرتين وتلاقي القيم اتغيرت في النص بسبب تعديل Transaction تانية\r\n\r\n\r\nPhantom Read: تعمل نفس الاستعلام مرتين وتطلع صفوف جديدة ظهرت فجأة في المرة التانية\r\nدي مشاكل حقيقية وبتسبب أخطاء صعبة تتعقّبها لو مش واخد بالك منها كويس.\r\n\r\n⚙️ ثالثًا: مستويات العزل (Isolation Levels)\r\nعشان نتحكم في التداخل ده، MySQL بيقدّم 4 مستويات مختلفة من العزل.\r\n كل مستوى بيفرض درجة مختلفة من الحماية بين المعاملات اللي شغالة في نفس الوقت:\r\nRead Uncommitted\r\nكل عملية تقدر تشوف تغييرات العمليات التانية حتى لو لسه ما خلصتش.\r\nأسرع مستوى، بس ممكن تقرأ بيانات هتتلغي بعد ثانية — نادر جدًا حد بيستخدمه فعلًا.\r\n\r\n\r\nRead Committed\r\nكل عملية تقدر تشوف بس البيانات اللي اتأكدت فعلًا.\r\nبيمنع الـ Dirty Reads، بس ممكن لسه تشوف بيانات تتغير بين قراءتين.\r\n\r\n\r\nRepeatable Read (وده المستوى الافتراضي في InnoDB)\r\nلما العملية تقرأ صف، بتفضل تشوفه بنفس القيم طول عمر المعاملة حتى لو اتغير من عملية تانية في الخلفية.\r\nده يمنع الـ Non-Repeatable Reads، وكمان في محرك InnoDB فعليًا بيمنع \r\nالـ Phantom Reads عن طريق تقنية اسمها\r\n MVCC (Multi-Version Concurrency Control)،\r\n وده اللي بيخليه مستوى متوازن جدًا وآمن في أغلب الحالات العملية.\r\n\r\nSerializable\r\n\r\nأعلى درجة حماية: بيخلي المعاملات تتنفذ كأنها واحدة ورا التانية مش مع بعض.\r\n\r\n\r\nيمنع كل أنواع المشاكل، بس بيقلل جدًا من التوازي والأداء، فبنستخدمه بس في الحالات اللي السلامة المطلقة فيها أهم من السرعة (زي الأنظمة البنكية الحساسة جدًا).\r\n\r\n⚖️ رابعًا: التوازن بين الأداء وسلامة البيانات\r\nكل ما تعلّي مستوى العزل، بتحمي بياناتك أكتر… بس في المقابل عدد المعاملات اللي تقدر تشتغل في نفس الوقت بيقل.\r\n عشان كده، اختيار المستوى المناسب مش قرار عشوائي، ولا لازم دايمًا تستخدم أعلى مستوى.\r\nلو نظامك فيه عمليات مالية معقّدة، يبقى الحذر أهم من السرعة.\r\n لكن لو نظام تقارير للقراءة فقط، يبقى السرعة أهم والبيانات مش هتتغير.\r\n\r\n📝 خامسًا: طريقة الاستخدام\r\nممكن تحدد مستوى العزل اللي محتاجه قبل تبدأ المعاملة كده:\r\nSET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;\r\nSTART TRANSACTION;\r\n-- أوامرك هنا\r\nCOMMIT;\r\nولو حبيت، ممكن تغيّره افتراضيًا لكل قاعدة البيانات من إعدادات MySQL نفسها.\r\n\r\n📌 ملاحظة أخيرة:\r\nتقدر تغيّر مستوى العزل لكل Session منفصل، يعني كل اتصال بقاعدة البيانات يشتغل بمستوى مختلف حسب الحاجة.\r\n\r\nبعض السلوك ممكن يختلف حسب Storage Engine، فخلي بالك إن InnoDB هو اللي بيدعم مستويات العزل دي بالطريقة الكاملة.\r\n\r\n',NULL,'published','2025-09-20 11:46:40','2025-09-20 13:18:11');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','author','admin') DEFAULT 'user',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Mohamed Adel','mohamed.adel.code@gmail.com','$2y$12$A0gDeRTfi0OkK0FjorAobeTKm/RUau8mB0JtET7Lgcrd.1bTDFKr.','admin','2025-09-20 11:17:02'),(2,'Ahmed Fadl','ahmed@gmail.com','$2y$12$53mDZ9nRDq8mSOyiXltx5.Lf4AJJPz5tZcpDxLK4qYXESZdfL5FOK','author','2025-09-20 11:50:30'),(3,'Khaled Salah','Khaled@gmail.com','$2y$12$3FVj2Aq8W1HqiTBpRaE23.1M8m2P8Ac5s/kg3SkyRwD6MPKoceDeK','author','2025-09-20 11:51:45'),(4,'Mohamed','mohamed@gmail.com','$2y$12$zoL24uegadZf/6Qnpm.iyeVkbQhTQErees/QIdJojdViZEnLcwbYm','user','2025-09-22 08:11:42'),(5,'Ahmed','ahmed1@gmail.com','$2y$12$.8fPzKl/YjeuUvBFEOFfzeo.VG8bRQACdtlGADVQdoMZgNZNhd9RK','user','2025-09-22 08:24:40');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-22  8:54:27
