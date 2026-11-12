<?php
/*
* This file is part of the auto1-oss/service-api-client-bundle.
*
* (c) AUTO1 Group SE https://www.auto1-group.com
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
declare(strict_types=1);

namespace Auto1\ServiceAPIClientBundle\Tests\Service\Request\Fixtures;

use Auto1\ServiceAPIRequest\ServiceRequestInterface;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class RequestWithDateContext implements ServiceRequestInterface
{
    #[Context(normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    protected ?\DateTimeInterface $markupDate = null;

    protected ?\DateTimeInterface $validAt = null;

    public function getMarkupDate(): ?\DateTimeInterface
    {
        return $this->markupDate;
    }

    public function setMarkupDate(?\DateTimeInterface $markupDate): self
    {
        $this->markupDate = $markupDate;

        return $this;
    }

    public function getValidAt(): ?\DateTimeInterface
    {
        return $this->validAt;
    }

    public function setValidAt(?\DateTimeInterface $validAt): self
    {
        $this->validAt = $validAt;

        return $this;
    }
}
