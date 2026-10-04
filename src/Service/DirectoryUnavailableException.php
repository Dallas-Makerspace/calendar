<?php
namespace App\Service;

/**
 * Thrown when Active Directory cannot be reached for a read-only lookup.
 */
class DirectoryUnavailableException extends \RuntimeException
{
}
