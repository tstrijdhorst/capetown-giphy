<?php

namespace Capetown\Commands\Giphy;

use Capetown\Core\CommandInterface;
use Capetown\Core\KeybaseAPIClient;
use Capetown\Core\Message;

class GiphyCommand implements CommandInterface {
	
	/**
	 * @var GiphyAPIClient
	 */
	private $giphyAPIClient;
	/**
	 * @var KeybaseAPIClient
	 */
	private $keybaseAPIClient;
	
	public function __construct(KeybaseAPIClient $keybaseAPIClient, GiphyAPIClient $giphyAPIClient) {
		$this->keybaseAPIClient = $keybaseAPIClient;
		$this->giphyAPIClient   = $giphyAPIClient;
	}
	
	public static function createDefault(KeybaseAPIClient $keybaseAPIClient): CommandInterface {
		return new self($keybaseAPIClient, new GiphyAPIClient(getenv('GIPHY_API_KEY')));
	}
	
	public static function getName(): string {
		return 'giphy';
	}
	
	public function handleMessage(Message $message): void {
		$searchQuery = $message->getBody();
		try {
			$randomGifPath = $this->giphyAPIClient->getRandomGif($searchQuery);
			$this->keybaseAPIClient->uploadAttachment($message->getChannel(), $randomGifPath, $searchQuery);
			unlink($randomGifPath);
		}
		catch (NoSearchResultsFoundException $e) {
			$this->keybaseAPIClient->sendMessage($message->getChannel(), $e->getMessage());
		}
	}
}