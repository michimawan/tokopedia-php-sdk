<?php

declare(strict_types=1);

namespace Gandung\Tokopedia\Auth;

use RuntimeException;
use Gandung\Tokopedia\AbstractService;
use Gandung\Tokopedia\Credential\Credential;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\RequestExceptionInterface;

use function base64_encode;
use function json_decode;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class Authorization extends AbstractService implements AuthorizationInterface
{
	/**
	 * @var CacheItemPoolInterface
	 */
	private $cachePool;

	/**
	 * @var CredentialInterface
	 */
	private $currentCredential;

	/**
	 * @var string
	 */
	private $cacheTag = 'authorization_metadata';

	/**
	 * @param CacheItemPoolInterface $cachePool
	 * @param array $config
	 * @return void
	 */
	public function __construct(CacheItemPoolInterface $cachePool, array $config)
	{
		parent::__construct($config);
		$this->setCachePool($cachePool);
	}

	/**
	 * {@inheritdoc}
	 */
	public function authorize()
	{
		$currentCredential = $this->getCurrentCredentialFromCache();

		if (null === $currentCredential) {
			$this->putCurrentCredentialIntoCache(
				json_decode(
					$this->fetchAuthorizationMetadata(),
					true
				)
			);
		}

		return ($currentCredential instanceof Credential)
			? $currentCredential
			: new Credential($currentCredential);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCachePool()
	{
		return $this->cachePool;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCachePool(CacheItemPoolInterface $cachePool)
	{
		$this->cachePool = $cachePool;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCacheTag(string $cacheTag)
	{
		$this->cacheTag = $cacheTag;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCacheTag()
	{
		return $this->cacheTag;
	}

	/**
	 * Fetch current credential from cache pool, if any.
	 *
	 * @return null|CredentialInterface
	 */
	private function getCurrentCredentialFromCache()
	{
		$credential = $this->getCachePool()->getItem($this->getCacheTag());

		return false === $credential->isHit()
			? null
			: new Credential($credential->get());
	}

	/**
	 * Save current credential into cache pool.
	 *
	 * @param array $credential
	 * @return bool
	 */
	private function putCurrentCredentialIntoCache(array $credential)
	{
		$cacheObject = $this->getCachePool()->getItem($this->getCacheTag());

		// set cache item metadata
		$cacheObject->set($credential);
		$cacheObject->expiresAfter($credential['expires_in']);

		// persist cache item
		return $this->getCachePool()->save($cacheObject);
	}

	/**
	 * Fetch authorization metadata from server.
	 *
	 * @return string
	 * @throws RuntimeException When http exception occur.
	 */
	private function fetchAuthorizationMetadata()
	{
		$headers = [
			'Authorization' => sprintf('Basic %s', $this->getSerializedBasicCredential())
		];

		try {
			return $this->getHttpClient()->request(
				'POST',
				'/token?grant_type=client_credentials',
				[
					'headers' => $headers,
					'verify'  => true,
					'version' => 2
				]
			);
		} catch (RequestExceptionInterface $e) {
			throw new RuntimeException(
				sprintf(
					"Caught a http exception. Code: %d, Reason: %s",
					$e->getResponse()->getStatusCode(),
					$e->getResponse()->getReasonPhrase()
				)
			);
		}
	}

	/**
	 * Get serialized basic credential in form <client_id>:<client_secret>
	 *
	 * @return string
	 */
	private function getSerializedBasicCredential()
	{
		return base64_encode(sprintf('%s:%s', $this->getClientID(), $this->getClientSecret()));
	}
}
