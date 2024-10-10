<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class OdooService
{
  private HttpService $httpService;
  private RequestStack $requestStack;
  private string $odooUrl;
  private string $odooDb;
  private string $odooUser;
  private string $odooPassword;

  public function __construct(
    HttpService $httpService,
    RequestStack $requestStack,
    string $odooUrl,
    string $odooDb,
    string $odooUser,
    string $odooPassword
  ) {
    $this->httpService  = $httpService;
    $this->requestStack = $requestStack;
    $this->odooUrl      = $odooUrl;
    $this->odooDb       = $odooDb;
    $this->odooUser     = $odooUser;
    $this->odooPassword = $odooPassword;
  }

  private function getSession()
  {
    return $this->requestStack->getCurrentRequest()->getSession();
  }

  private function odooAuthenticate()
  {
    $session = $this->getSession();

    if ($session->has('odoo_uid')) {
      return $session->get('odoo_uid');
    }

    $uid     = $this->httpService->odooCall($this->odooUrl, 'common', 'authenticate', [
      $this->odooDb,
      $this->odooUser,
      $this->odooPassword,
      []
    ]);

    $session->set('odoo_uid', $uid);

    return $uid;
  }

  public function getOdooModelData($model, $context = [], $fields = [])
  {
    return $this->httpService->odooCall($this->odooUrl, 'object', 'execute_kw', [
      $this->odooDb,
      $this->odooAuthenticate(),
      $this->odooPassword,
      $model,
      'search_read',
      [$context],
      ['fields' => $fields]
    ]);
  }

  public function createOdooPartner(string $model, array $partnerData): int
  {
    return $this->httpService->odooCall($this->odooUrl, 'object', 'execute_kw', [
      $this->odooDb,
      $this->odooAuthenticate(),
      $this->odooPassword,
      $model,
      'create',
      [$partnerData]
    ]);
  }

  public function updateOdooPartner(string $model, int $partnerId, array $fieldsToUpdate): bool
  {
    return $this->httpService->odooCall($this->odooUrl, 'object', 'execute_kw', [
      $this->odooDb,
      $this->odooAuthenticate(),
      $this->odooPassword,
      $model,
      'write',
      [[$partnerId], $fieldsToUpdate]
    ]);
  }

  public function deleteOdooPartner(string $model, int $partnerId): bool
  {
    return $this->httpService->odooCall($this->odooUrl, 'object', 'execute_kw', [
      $this->odooDb,
      $this->odooAuthenticate(),
      $this->odooPassword,
      $model,
      'unlink',
      [[$partnerId]]
    ]);
  }
}
