<?php

require './vendor/autoload.php';
require './Mailer.php';


$method = $_SERVER["REQUEST_METHOD"];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


function render($path, $params = []): string
{
	ob_start();
	require __DIR__ . "/views/" . $path;
	return ob_get_clean();
}

function home()
{
	echo render("home.phtml", [
		'isSuccess' => isset($_GET["sentIsSuccess"])
	]);
}

function submitMessageHandler()
{
	$pdo = getConnection();
	// El küldjük az adatbázisnak az email, tárgy, body, statusz, próbálkozások száma, létrehozás ideje 
	$statement = $pdo->prepare("INSERT INTO `messages`
    (`email`, `subject`, `body`, `status`, `numberOfAttempts`, `createdAt`) 
  VALUES 
    (?, ?, ?, ?, ?, ?);");


	// Mielőtt megtesszük ezt , létre kell hoznunk a body-t amire egy általunk létrehozott temlate-t kell használni.

	$body = render("email-template.phtml", [
		'name' => $_POST['name'],
		'email' => $_POST['email'],
		'content' => $_POST['content'],
	]);

	// Ha létrejött a body, akkor betáplálhatjuk az execute függvénynek az egyes értékeket.

	$statement->execute([
		$_SERVER['RECIPIENT_EMAIL'], // Ahova szeretnéd küldeni az emialt
		"Új üzenete érkezett", //Megjelenített tárgys 
		$body, // Megkomponált body
		'notSent', // Státusz
		0, // Próbálkozások száma
		time() // És létrehozás ideje
	]);

	header("Location: /learn_php_advanced/3.Send_Email/mailer/server?sentIsSuccess=1#contactForm");
}



function sendMailsHandler()
{
	$pdo = getConnection();
	$statement = $pdo->prepare(
		"SELECT * FROM messages /**VÁLASSZUNK KI MINDEN REKORDOT A MESSAGES-BŐL */ 
		WHERE		/**AHOL */ 
		status = 'notSent' AND /**STATUS = 'notSent' -el */ 
		numberOfAttempts < 10 /** ÉS próbálkozásokszáma kissebb mint 10 */
		ORDER BY createdAt ASC"
		/**Létrehozás időpontja által növekvőbe állítva */
	);

	$statement->execute([]); // Lefuttatjuk a statementet
	$messages = $statement->fetchAll(PDO::FETCH_ASSOC); // És lefetcheljük a messages-t

	foreach ($messages as $message) { // Át iterálunk a messages-en
		$pdo = getConnection(); // PDO connection létrehozása 
		$statement = $pdo->prepare(
			"UPDATE `messages` SET  /**Mielőtt elküldjük az emailt, update-jük a messages tábla  */
			`status` = 'sending',  /**status-át sending-re */
			numberOfAttempts = ?  /**Próbálkozások számát növeljük egyel */
			WHERE  /** Ahol az id egyenlő a messages[id]jával */
			id = ?"
		);

		$statement->execute([
			(int)$message["numberOfAttempts"] + 1,
			$message["id"]
		]); // Lefuttatjuk

		$isSent = sendMail( // Majd elküldjük az emailt ami várja a 
			$message["email"], // emailt
			$message["subject"], // tárgyat
			$message["body"] // bodyt
		);

		// Ez az isSent visszatér egy bool-al függően attól hogy a küldés sikeres volt-e vagy sem

		if($isSent) { // Ha sikeres lett,
			$statement = $pdo->prepare(
				"UPDATE `messages` SET status = `sent`, sentAt = ? WHERE id = ?"
			);

			$statement->execute([
				time(),
				$message["id"]
			]);
		} else {
			$statement = $pdo->prepare(
				"UPDATE `messages` SET status = `notSent` WHERE id = ?"
			);
			$statement->execute([$message["id"]]);
		}

	};
}












function notFoundHandler()
{
	header("Location: /learn_php_advanced/3.Send_Email/mailer/server/");
}



function getConnection()
{
	return new PDO(
		'mysql:host=' . $_SERVER['DB_HOST'] . ';dbname=' . $_SERVER['DB_NAME'],
		$_SERVER['DB_USER'],
		$_SERVER['DB_PASSWORD']
	);
}



$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
	$r->addRoute('GET', '/learn_php_advanced/3.Send_Email/mailer/server/', 'home');
	$r->addRoute('POST', '/learn_php_advanced/3.Send_Email/mailer/server/submit-message', 'submitMessageHandler');
	$r->addRoute('POST', '/learn_php_advanced/3.Send_Email/mailer/server/send-mails', 'sendMailsHandler');
});


$routeInfo = $dispatcher->dispatch($method, $path);
switch ($routeInfo[0]) {
	case FastRoute\Dispatcher::NOT_FOUND:
		notFoundHandler();
		break;
	case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
		$allowedMethods = $routeInfo[1];
		notFoundHandler();
		break;
	case FastRoute\Dispatcher::FOUND:
		$handler = $routeInfo[1];
		$vars = $routeInfo[2];
		$handler($vars);
		break;
}
