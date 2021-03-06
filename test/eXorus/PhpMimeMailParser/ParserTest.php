<?php

namespace Test\eXorus\PhpMimeMailParser;

use eXorus\PhpMimeMailParser\Parser;
use eXorus\PhpMimeMailParser\Attachment;

require_once APP_SRC . 'Parser.php';

/*
* Class Test : MimeMailParserTest
*
* Liste des mails :
* m0001 : mail avec un fichier attaché de 1 ko
* m0002 : mail avec un fichier attaché de 3 ko
* m0003 : mail avec un fichier attaché de 14 ko
* m0004 : mail avec un fichier attaché de 800 ko
* m0005 : mail avec un fichier attaché de 1 500 ko
* m0006 : mail avec un fichier attaché de 3 196 ko
* m0007 : mail avec un fichier attaché sans content-disposition
* m0008 : mail avec des fichiers attachés avec content-id
*/

class ParserTest extends \PHPUnit_Framework_TestCase {
	
	/**
	* @dataProvider provideMails
	*/
	function testGetAttachmentsWithText($mid, $nbAttachments, $size, $subject){
				
		$file = __DIR__."/mails/".$mid;
		$fd = fopen($file, "r");
		$contents = fread($fd, filesize($file));
		fclose($fd);

		$Parser = new Parser();
		$Parser->setText($contents);

		$this->assertEquals($subject,$Parser->getHeader('subject'));

		$attachments = $Parser->getAttachments();

		$this->assertEquals($nbAttachments,count($attachments));
		
		if($size != NULL){
			$attach_dir = __DIR__."/mails/attach_".$mid."/";
			$Parser->saveAttachments($attach_dir, "");

			$this->assertEquals($size,filesize($attach_dir.$attachments[0]->getFilename()));
			unlink($attach_dir.$attachments[0]->getFilename());

			rmdir($attach_dir);
		}
	}

	/**
	* @dataProvider provideMails
	*/
	function testGetAttachmentsWithPath($mid, $nbAttachments, $size, $subject){

		$file = __DIR__."/mails/".$mid;

		$Parser = new Parser();
		$Parser->setPath($file);

		$this->assertEquals($subject,$Parser->getHeader('subject'));

		$attachments = $Parser->getAttachments();

		$this->assertEquals($nbAttachments,count($attachments));

		if($size != NULL){
			$attach_dir = __DIR__."/mails/attach_".$mid."/";
			$Parser->saveAttachments($attach_dir, "");

			$this->assertEquals($size,filesize($attach_dir.$attachments[0]->getFilename()));
			unlink($attach_dir.$attachments[0]->getFilename());

			rmdir($attach_dir);
		}
	}

	function provideMails(){
		$mails = array(
			array('m0001',1,2, 'Mail avec fichier attaché de 1ko'),
			array('m0002',1,2229, 'Mail avec fichier attaché de 3ko'),
			array('m0003',1,13369, 'Mail de 14 Ko'),
			array('m0004',1,817938, 'Mail de 800ko'),
			array('m0005',1,1635877, 'Mail de 1500 Ko'),
			array('m0006',1,3271754, 'Mail de 3 196 Ko'),
			array('m0007',1,2229, 'Mail avec fichier attaché de 3ko'),
			array('m0008',3,NULL, 'Testing MIME E-mail composing with cid'),
			array('m0010',1,817938, 'Mail de 800ko without filename')
		);
		return $mails;
	}

	function testGetAttachmentsWithContentId(){
		$mid = "m0008";

		$file = __DIR__."/mails/".$mid;

		$Parser = new Parser();
		$Parser->setPath($file);

		$attach_dir = __DIR__."/mails/attach_".$mid."/";
		$attach_url = "http://www.company.com/attachments/".$mid."/";
		$Parser->saveAttachments($attach_dir, $attach_url);

		$html_embedded = $Parser->getMessageBody('html', TRUE);

		$this->assertEquals(2,substr_count($html_embedded, $attach_url));
		unlink($attach_dir.'attachment.txt');
		unlink($attach_dir.'background.jpg');
		unlink($attach_dir.'logo.jpg');
		rmdir($attach_dir);
	}
	
	function testWithoutCharset(){
		// Issue 7
		$mid = "m0009";
		$file = __DIR__."/mails/".$mid;
		$Parser = new Parser();
		$Parser->setPath($file);
		$Parser->getMessageBody('text');
		$Parser->getMessageBody('html');
	}
}
?>

