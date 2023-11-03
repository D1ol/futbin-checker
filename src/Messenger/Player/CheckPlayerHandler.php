<?php

declare(strict_types=1);

namespace App\Messenger\Player;

use App\Calculator\PlayerProfitCalculator;
use App\Core\Messenger\HandlerResult;
use App\Core\Serializer\Denormalizer\SalesDenormalizer;
use App\Model\Player\BaseCardPrices;
use App\Model\Player\BaseCardSales;
use App\Model\Player\ReferencedCard;
use App\Model\Player\Sales\Sale;
use App\Notifier\Factory\ProfitMessageFactory;
use App\Repository\Player\PlayerRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use XOne\Bundle\NotifierBundle\Sender\MessageSenderInterface;

#[AsMessageHandler]
class CheckPlayerHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private HttpClientInterface $futbinHttpClient,
        private SerializerInterface $serializer,
        private PlayerRepository $playerRepository,
        private MessageBusInterface $messageBus,
        private MessageSenderInterface $messageSender,
        private ProfitMessageFactory $profitMessageFactory
    ) {
    }

    public function __invoke(CheckPlayerMessage $checkPlayer): HandlerResult
    {
        $player = $this->playerRepository->findOneBy(['baseId' => $checkPlayer->getBaseId()]);

        $playerPricesRequest = $this->getPlayerPrices($checkPlayer->getBaseId());
        $response = $this->serializer->deserialize($playerPricesRequest, ReferencedCard::class.'[]', JsonEncoder::FORMAT);
        $baseCardPrices = new BaseCardPrices($response);

        $playerSalesRequest = $this->getPlayerSales($checkPlayer->getBaseId());
        $response = $this->serializer->deserialize($playerSalesRequest, Sale::class.'[]', JsonEncoder::FORMAT, [SalesDenormalizer::SALES_RESPONSE => true]);
        $baseCardSales = new BaseCardSales($response);

        $firstPrice = $baseCardPrices->getMainCard()->getPrices()->getPs()->getFirstPriceFloat();
        $playerProfitCalculator = new PlayerProfitCalculator($firstPrice, $baseCardSales->getAverage());

        if ($playerProfitCalculator->isDiscount()) {

            $message = $this->profitMessageFactory->createProfitMessage(
                name: $player->getName(),
                profit: $playerProfitCalculator->getExpectedProfit(),
                transport: 'telegram');
            $this->messageSender->send($message);

        }

        $string = sprintf('baseId: %s', $checkPlayer->getBaseId());
        $this->logger->alert($string);
        $this->logger->alert(sprintf('exec time %s:', (new \DateTimeImmutable('now'))->format('H:i:s')));

        $nextCheckDate = $baseCardPrices->getMainCard()->getPrices()->getPs()->getNextCheckDate();
        $this->messageBus->dispatch(new CheckPlayerMessage($checkPlayer->getBaseId()), [DelayStamp::delayUntil($nextCheckDate)]);

        return new HandlerResult($string.' added to queue');
    }

    public function getPlayerPrices(int $baseId): string
    {
         return $this->futbinHttpClient->request('GET', 'playerPrices', ['query' => [
            'player' => $baseId,
        ]])->getContent();

        return $data = '{
  "194765": {
    "prices": {
      "ps": {
        "LCPrice": "115,000",
        "LCPrice2": "122,000",
        "LCPrice3": "122,000",
        "LCPrice4": "122,000",
        "LCPrice5": "122,000",
        "updated": "2 mins ago",
        "MinPrice": "11,000",
        "MaxPrice": "210,000",
        "PRP": "55",
        "LCPClosing": 119000
      },
      "pc": {
        "LCPrice": "153,000",
        "LCPrice2": "153,000",
        "LCPrice3": "156,000",
        "LCPrice4": "157,000",
        "LCPrice5": "157,000",
        "updated": "9 mins ago",
        "MinPrice": "13,750",
        "MaxPrice": "260,000",
        "PRP": "56",
        "LCPClosing": 157000
      }
    }
  },
  "50526413": {
    "prices": {
      "ps": {
        "LCPrice": "636,000"
      },
      "pc": {
        "LCPrice": "755,000"
      }
    }
  },
  "67303629": {
    "prices": {
      "ps": {
        "LCPrice": "680,000"
      },
      "pc": {
        "LCPrice": "790,000"
      }
    }
  }
}';
    }

    public function getPlayerSales(int $baseId): string
    {
        return $this->futbinHttpClient->request('GET', 'getPlayerSales', ['query' => [
            'resourceId' => $baseId,
            'platform' => 'ps4'
        ]])->getContent();
        return '[
    {
        "unix_date": 1698415937,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 15:13:18"
    },
    {
        "unix_date": 1698415936,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 15:13:18"
    },
    {
        "unix_date": 1698415931,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 15:13:12"
    },
    {
        "unix_date": 1698415923,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:13:05"
    },
    {
        "unix_date": 1698415917,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 15:12:58"
    },
    {
        "unix_date": 1698415917,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:12:58"
    },
    {
        "unix_date": 1698415913,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 15:12:54"
    },
    {
        "unix_date": 1698415911,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:12:52"
    },
    {
        "unix_date": 1698415894,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:12:35"
    },
    {
        "unix_date": 1698415846,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:11:47"
    },
    {
        "unix_date": 1698415777,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 15:10:39"
    },
    {
        "unix_date": 1698415694,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:09:16"
    },
    {
        "unix_date": 1698415685,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 15:09:07"
    },
    {
        "unix_date": 1698415682,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 15:09:04"
    },
    {
        "unix_date": 1698415674,
        "Price": 119000,
        "BIN": 130000,
        "status": "closed",
        "updated": "2023-10-27 15:08:56"
    },
    {
        "unix_date": 1698415674,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:08:55"
    },
    {
        "unix_date": 1698415674,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:08:56"
    },
    {
        "unix_date": 1698415442,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 15:05:03"
    },
    {
        "unix_date": 1698415433,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 15:04:54"
    },
    {
        "unix_date": 1698415432,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:04:53"
    },
    {
        "unix_date": 1698415402,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 15:04:23"
    },
    {
        "unix_date": 1698415372,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:03:53"
    },
    {
        "unix_date": 1698415369,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:03:50"
    },
    {
        "unix_date": 1698415341,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:03:22"
    },
    {
        "unix_date": 1698415286,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:02:27"
    },
    {
        "unix_date": 1698415252,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 15:01:53"
    },
    {
        "unix_date": 1698415213,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:01:14"
    },
    {
        "unix_date": 1698415206,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:01:07"
    },
    {
        "unix_date": 1698415195,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:00:56"
    },
    {
        "unix_date": 1698415189,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:00:50"
    },
    {
        "unix_date": 1698415186,
        "Price": 118000,
        "BIN": 118000,
        "status": "closed",
        "updated": "2023-10-27 15:00:48"
    },
    {
        "unix_date": 1698415185,
        "Price": 118000,
        "BIN": 118000,
        "status": "closed",
        "updated": "2023-10-27 15:00:46"
    },
    {
        "unix_date": 1698415170,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 15:00:31"
    },
    {
        "unix_date": 1698415156,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:00:17"
    },
    {
        "unix_date": 1698415150,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 15:00:11"
    },
    {
        "unix_date": 1698415145,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 15:00:06"
    },
    {
        "unix_date": 1698415143,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 15:00:04"
    },
    {
        "unix_date": 1698415138,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 14:59:59"
    },
    {
        "unix_date": 1698415138,
        "Price": 119000,
        "BIN": 131000,
        "status": "closed",
        "updated": "2023-10-27 15:00:00"
    },
    {
        "unix_date": 1698415127,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 14:59:48"
    },
    {
        "unix_date": 1698415125,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:59:46"
    },
    {
        "unix_date": 1698415104,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:59:26"
    },
    {
        "unix_date": 1698415083,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:59:04"
    },
    {
        "unix_date": 1698415073,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:58:54"
    },
    {
        "unix_date": 1698415061,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:58:43"
    },
    {
        "unix_date": 1698415054,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:58:35"
    },
    {
        "unix_date": 1698415051,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:58:33"
    },
    {
        "unix_date": 1698415032,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:58:13"
    },
    {
        "unix_date": 1698414986,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:57:27"
    },
    {
        "unix_date": 1698414947,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:56:49"
    },
    {
        "unix_date": 1698414938,
        "Price": 120000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:56:40"
    },
    {
        "unix_date": 1698414819,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:54:40"
    },
    {
        "unix_date": 1698414812,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 14:54:33"
    },
    {
        "unix_date": 1698414804,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 14:54:25"
    },
    {
        "unix_date": 1698414798,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:54:19"
    },
    {
        "unix_date": 1698414791,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:54:12"
    },
    {
        "unix_date": 1698414783,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:54:04"
    },
    {
        "unix_date": 1698414778,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:53:59"
    },
    {
        "unix_date": 1698414763,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:53:47"
    },
    {
        "unix_date": 1698414759,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:53:43"
    },
    {
        "unix_date": 1698414752,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:53:36"
    },
    {
        "unix_date": 1698414740,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 14:53:22"
    },
    {
        "unix_date": 1698414736,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:53:19"
    },
    {
        "unix_date": 1698414731,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:53:12"
    },
    {
        "unix_date": 1698414717,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:52:59"
    },
    {
        "unix_date": 1698414713,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:52:54"
    },
    {
        "unix_date": 1698414711,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:52:53"
    },
    {
        "unix_date": 1698414710,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:52:52"
    },
    {
        "unix_date": 1698414698,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:52:40"
    },
    {
        "unix_date": 1698414677,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:52:19"
    },
    {
        "unix_date": 1698414633,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:51:35"
    },
    {
        "unix_date": 1698414472,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 14:48:53"
    },
    {
        "unix_date": 1698414469,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:48:51"
    },
    {
        "unix_date": 1698414443,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:48:25"
    },
    {
        "unix_date": 1698414431,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:48:12"
    },
    {
        "unix_date": 1698414381,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:47:22"
    },
    {
        "unix_date": 1698414242,
        "Price": 121000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:45:05"
    },
    {
        "unix_date": 1698414207,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 14:44:28"
    },
    {
        "unix_date": 1698414196,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 14:44:17"
    },
    {
        "unix_date": 1698414174,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:43:55"
    },
    {
        "unix_date": 1698414164,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:43:45"
    },
    {
        "unix_date": 1698414071,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:42:13"
    },
    {
        "unix_date": 1698414061,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:42:03"
    },
    {
        "unix_date": 1698414059,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:42:00"
    },
    {
        "unix_date": 1698414013,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:41:15"
    },
    {
        "unix_date": 1698414010,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:41:13"
    },
    {
        "unix_date": 1698414007,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:41:09"
    },
    {
        "unix_date": 1698413949,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:40:10"
    },
    {
        "unix_date": 1698413947,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:40:09"
    },
    {
        "unix_date": 1698413945,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:40:07"
    },
    {
        "unix_date": 1698413943,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:40:04"
    },
    {
        "unix_date": 1698413943,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:40:04"
    },
    {
        "unix_date": 1698413942,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:40:03"
    },
    {
        "unix_date": 1698413937,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:39:58"
    },
    {
        "unix_date": 1698413868,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:38:50"
    },
    {
        "unix_date": 1698413823,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:38:04"
    },
    {
        "unix_date": 1698413810,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:37:52"
    },
    {
        "unix_date": 1698413805,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:37:47"
    },
    {
        "unix_date": 1698413802,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:37:44"
    },
    {
        "unix_date": 1698413788,
        "Price": 122000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:37:30"
    },
    {
        "unix_date": 1698413692,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:35:54"
    },
    {
        "unix_date": 1698413691,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:35:52"
    },
    {
        "unix_date": 1698413664,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:35:25"
    },
    {
        "unix_date": 1698413623,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:34:45"
    },
    {
        "unix_date": 1698413615,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:34:37"
    },
    {
        "unix_date": 1698413527,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:33:08"
    },
    {
        "unix_date": 1698413489,
        "Price": 0,
        "BIN": 126000,
        "status": "expired",
        "updated": "2023-10-27 14:32:31"
    },
    {
        "unix_date": 1698413425,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:31:26"
    },
    {
        "unix_date": 1698413400,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:31:01"
    },
    {
        "unix_date": 1698413273,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:28:55"
    },
    {
        "unix_date": 1698413231,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 14:28:13"
    },
    {
        "unix_date": 1698413231,
        "Price": 117000,
        "BIN": 117000,
        "status": "closed",
        "updated": "2023-10-27 14:28:12"
    },
    {
        "unix_date": 1698413229,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:28:10"
    },
    {
        "unix_date": 1698413214,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 14:27:55"
    },
    {
        "unix_date": 1698413203,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:27:44"
    },
    {
        "unix_date": 1698413193,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:27:35"
    },
    {
        "unix_date": 1698413163,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:27:05"
    },
    {
        "unix_date": 1698413101,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:26:03"
    },
    {
        "unix_date": 1698413094,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:25:55"
    },
    {
        "unix_date": 1698413072,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:25:33"
    },
    {
        "unix_date": 1698413055,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:25:16"
    },
    {
        "unix_date": 1698413003,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:24:25"
    },
    {
        "unix_date": 1698412997,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:24:19"
    },
    {
        "unix_date": 1698412945,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:23:27"
    },
    {
        "unix_date": 1698412920,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 14:23:01"
    },
    {
        "unix_date": 1698412905,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:22:46"
    },
    {
        "unix_date": 1698412859,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:22:01"
    },
    {
        "unix_date": 1698412818,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:21:20"
    },
    {
        "unix_date": 1698412810,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:21:12"
    },
    {
        "unix_date": 1698412810,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:21:11"
    },
    {
        "unix_date": 1698412788,
        "Price": 0,
        "BIN": 130000,
        "status": "expired",
        "updated": "2023-10-27 14:20:50"
    },
    {
        "unix_date": 1698412771,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:20:33"
    },
    {
        "unix_date": 1698412719,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:19:49"
    },
    {
        "unix_date": 1698412693,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:19:14"
    },
    {
        "unix_date": 1698412663,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 14:19:01"
    },
    {
        "unix_date": 1698412648,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:18:29"
    },
    {
        "unix_date": 1698412641,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 14:18:24"
    },
    {
        "unix_date": 1698412627,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:18:09"
    },
    {
        "unix_date": 1698412536,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:16:38"
    },
    {
        "unix_date": 1698412467,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 14:15:28"
    },
    {
        "unix_date": 1698412451,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:15:12"
    },
    {
        "unix_date": 1698412323,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 14:13:04"
    },
    {
        "unix_date": 1698412234,
        "Price": 120000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:11:35"
    },
    {
        "unix_date": 1698412204,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:11:05"
    },
    {
        "unix_date": 1698412200,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:11:02"
    },
    {
        "unix_date": 1698412189,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:10:52"
    },
    {
        "unix_date": 1698412178,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:10:40"
    },
    {
        "unix_date": 1698412177,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:10:39"
    },
    {
        "unix_date": 1698412176,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:10:37"
    },
    {
        "unix_date": 1698412162,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:10:23"
    },
    {
        "unix_date": 1698412160,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:10:21"
    },
    {
        "unix_date": 1698412129,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:09:50"
    },
    {
        "unix_date": 1698411989,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:07:31"
    },
    {
        "unix_date": 1698411979,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:07:21"
    },
    {
        "unix_date": 1698411977,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 14:07:18"
    },
    {
        "unix_date": 1698411976,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:07:17"
    },
    {
        "unix_date": 1698411975,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:07:17"
    },
    {
        "unix_date": 1698411974,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:07:15"
    },
    {
        "unix_date": 1698411970,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:07:11"
    },
    {
        "unix_date": 1698411962,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:07:03"
    },
    {
        "unix_date": 1698411954,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:06:55"
    },
    {
        "unix_date": 1698411950,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:06:51"
    },
    {
        "unix_date": 1698411928,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 14:06:29"
    },
    {
        "unix_date": 1698411917,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:06:18"
    },
    {
        "unix_date": 1698411859,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:05:20"
    },
    {
        "unix_date": 1698411853,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:05:15"
    },
    {
        "unix_date": 1698411757,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:03:39"
    },
    {
        "unix_date": 1698411750,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 14:03:32"
    },
    {
        "unix_date": 1698411739,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 14:03:20"
    },
    {
        "unix_date": 1698411737,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 14:03:18"
    },
    {
        "unix_date": 1698411730,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:03:12"
    },
    {
        "unix_date": 1698411712,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:02:53"
    },
    {
        "unix_date": 1698411709,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:02:50"
    },
    {
        "unix_date": 1698411691,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:02:32"
    },
    {
        "unix_date": 1698411688,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 14:02:30"
    },
    {
        "unix_date": 1698411636,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 14:01:38"
    },
    {
        "unix_date": 1698411559,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 14:00:21"
    },
    {
        "unix_date": 1698411381,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:57:23"
    },
    {
        "unix_date": 1698411376,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:57:18"
    },
    {
        "unix_date": 1698411368,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 13:57:10"
    },
    {
        "unix_date": 1698411214,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:54:35"
    },
    {
        "unix_date": 1698411171,
        "Price": 120000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 13:53:54"
    },
    {
        "unix_date": 1698411168,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 13:53:51"
    },
    {
        "unix_date": 1698411149,
        "Price": 120000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 13:53:31"
    },
    {
        "unix_date": 1698411146,
        "Price": 118000,
        "BIN": 118000,
        "status": "closed",
        "updated": "2023-10-27 13:53:28"
    },
    {
        "unix_date": 1698411137,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 13:53:19"
    },
    {
        "unix_date": 1698411131,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 13:53:13"
    },
    {
        "unix_date": 1698410984,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:50:46"
    },
    {
        "unix_date": 1698410982,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:50:43"
    },
    {
        "unix_date": 1698410964,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 13:50:26"
    },
    {
        "unix_date": 1698410953,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:50:15"
    },
    {
        "unix_date": 1698410900,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:49:22"
    },
    {
        "unix_date": 1698410887,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:49:08"
    },
    {
        "unix_date": 1698410767,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 13:47:09"
    },
    {
        "unix_date": 1698410766,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 13:47:09"
    },
    {
        "unix_date": 1698410765,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 13:47:07"
    },
    {
        "unix_date": 1698410674,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 13:45:35"
    },
    {
        "unix_date": 1698410593,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:44:14"
    },
    {
        "unix_date": 1698410552,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:43:33"
    },
    {
        "unix_date": 1698410546,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:43:28"
    },
    {
        "unix_date": 1698410543,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:43:24"
    },
    {
        "unix_date": 1698410537,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:43:19"
    },
    {
        "unix_date": 1698410534,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:43:15"
    },
    {
        "unix_date": 1698410528,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:43:10"
    },
    {
        "unix_date": 1698410523,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:43:04"
    },
    {
        "unix_date": 1698410508,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 13:42:49"
    },
    {
        "unix_date": 1698410491,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 13:42:33"
    },
    {
        "unix_date": 1698410479,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:42:20"
    },
    {
        "unix_date": 1698410452,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:41:53"
    },
    {
        "unix_date": 1698410411,
        "Price": 121000,
        "BIN": 127000,
        "status": "closed",
        "updated": "2023-10-27 13:41:13"
    },
    {
        "unix_date": 1698410407,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:41:08"
    },
    {
        "unix_date": 1698410397,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:40:58"
    },
    {
        "unix_date": 1698410397,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 13:40:59"
    },
    {
        "unix_date": 1698410390,
        "Price": 117000,
        "BIN": 130000,
        "status": "closed",
        "updated": "2023-10-27 13:40:53"
    },
    {
        "unix_date": 1698410347,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:40:08"
    },
    {
        "unix_date": 1698410271,
        "Price": 130000,
        "BIN": 130000,
        "status": "closed",
        "updated": "2023-10-27 13:38:53"
    },
    {
        "unix_date": 1698410201,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:37:42"
    },
    {
        "unix_date": 1698410192,
        "Price": 118000,
        "BIN": 118000,
        "status": "closed",
        "updated": "2023-10-27 13:37:33"
    },
    {
        "unix_date": 1698410190,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:37:31"
    },
    {
        "unix_date": 1698410188,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:37:29"
    },
    {
        "unix_date": 1698410186,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:37:27"
    },
    {
        "unix_date": 1698410179,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:37:20"
    },
    {
        "unix_date": 1698410173,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:37:14"
    },
    {
        "unix_date": 1698410167,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:37:08"
    },
    {
        "unix_date": 1698410163,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:37:04"
    },
    {
        "unix_date": 1698410162,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 13:37:03"
    },
    {
        "unix_date": 1698410160,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:37:01"
    },
    {
        "unix_date": 1698410087,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:35:48"
    },
    {
        "unix_date": 1698410074,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 13:35:35"
    },
    {
        "unix_date": 1698410063,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 13:35:25"
    },
    {
        "unix_date": 1698409987,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 13:34:08"
    },
    {
        "unix_date": 1698409967,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:33:49"
    },
    {
        "unix_date": 1698409946,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:33:28"
    },
    {
        "unix_date": 1698409939,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:33:21"
    },
    {
        "unix_date": 1698409928,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:33:09"
    },
    {
        "unix_date": 1698409845,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:31:46"
    },
    {
        "unix_date": 1698409834,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:31:36"
    },
    {
        "unix_date": 1698409805,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:31:07"
    },
    {
        "unix_date": 1698409789,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:30:50"
    },
    {
        "unix_date": 1698409751,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:30:13"
    },
    {
        "unix_date": 1698409744,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:30:06"
    },
    {
        "unix_date": 1698409647,
        "Price": 118000,
        "BIN": 118000,
        "status": "closed",
        "updated": "2023-10-27 13:28:28"
    },
    {
        "unix_date": 1698409644,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:28:26"
    },
    {
        "unix_date": 1698409569,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:27:10"
    },
    {
        "unix_date": 1698409563,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:27:04"
    },
    {
        "unix_date": 1698409486,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:25:48"
    },
    {
        "unix_date": 1698409477,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 13:25:39"
    },
    {
        "unix_date": 1698409349,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:23:30"
    },
    {
        "unix_date": 1698409341,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 13:23:23"
    },
    {
        "unix_date": 1698409340,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 13:23:22"
    },
    {
        "unix_date": 1698409339,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 13:23:21"
    },
    {
        "unix_date": 1698409339,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 13:23:21"
    },
    {
        "unix_date": 1698409334,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 13:23:16"
    },
    {
        "unix_date": 1698409333,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 13:23:15"
    },
    {
        "unix_date": 1698409326,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:23:08"
    },
    {
        "unix_date": 1698409303,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 13:22:44"
    },
    {
        "unix_date": 1698409284,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 13:22:25"
    },
    {
        "unix_date": 1698409247,
        "Price": 0,
        "BIN": 126000,
        "status": "expired",
        "updated": "2023-10-27 13:21:50"
    },
    {
        "unix_date": 1698409229,
        "Price": 120000,
        "BIN": 128000,
        "status": "closed",
        "updated": "2023-10-27 13:21:31"
    },
    {
        "unix_date": 1698409209,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 13:21:10"
    },
    {
        "unix_date": 1698409151,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:20:13"
    },
    {
        "unix_date": 1698409092,
        "Price": 112000,
        "BIN": 112000,
        "status": "closed",
        "updated": "2023-10-27 13:19:13"
    },
    {
        "unix_date": 1698408846,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 13:15:08"
    },
    {
        "unix_date": 1698408845,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 13:15:07"
    },
    {
        "unix_date": 1698408821,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:14:42"
    },
    {
        "unix_date": 1698408818,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 13:14:39"
    },
    {
        "unix_date": 1698408801,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:14:22"
    },
    {
        "unix_date": 1698408681,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 13:12:23"
    },
    {
        "unix_date": 1698408634,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:11:42"
    },
    {
        "unix_date": 1698408633,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:11:42"
    },
    {
        "unix_date": 1698408629,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:11:45"
    },
    {
        "unix_date": 1698408628,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:11:35"
    },
    {
        "unix_date": 1698408595,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:11:17"
    },
    {
        "unix_date": 1698408587,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 13:10:50"
    },
    {
        "unix_date": 1698408580,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:10:57"
    },
    {
        "unix_date": 1698408577,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:11:29"
    },
    {
        "unix_date": 1698408577,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:11:06"
    },
    {
        "unix_date": 1698408570,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:10:31"
    },
    {
        "unix_date": 1698408567,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:10:29"
    },
    {
        "unix_date": 1698408534,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:09:56"
    },
    {
        "unix_date": 1698408435,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 13:08:16"
    },
    {
        "unix_date": 1698408403,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:07:44"
    },
    {
        "unix_date": 1698408397,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:07:39"
    },
    {
        "unix_date": 1698408389,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:07:31"
    },
    {
        "unix_date": 1698408379,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:07:20"
    },
    {
        "unix_date": 1698408375,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:07:16"
    },
    {
        "unix_date": 1698408365,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:07:06"
    },
    {
        "unix_date": 1698408351,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:06:53"
    },
    {
        "unix_date": 1698408348,
        "Price": 118000,
        "BIN": 118000,
        "status": "closed",
        "updated": "2023-10-27 13:06:49"
    },
    {
        "unix_date": 1698408345,
        "Price": 118000,
        "BIN": 118000,
        "status": "closed",
        "updated": "2023-10-27 13:06:47"
    },
    {
        "unix_date": 1698408344,
        "Price": 117000,
        "BIN": 117000,
        "status": "closed",
        "updated": "2023-10-27 13:06:46"
    },
    {
        "unix_date": 1698408340,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:06:41"
    },
    {
        "unix_date": 1698408340,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 13:06:41"
    },
    {
        "unix_date": 1698408338,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:06:39"
    },
    {
        "unix_date": 1698408330,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 13:06:31"
    },
    {
        "unix_date": 1698408327,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:06:28"
    },
    {
        "unix_date": 1698408327,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 13:06:28"
    },
    {
        "unix_date": 1698408323,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:06:26"
    },
    {
        "unix_date": 1698408318,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:06:20"
    },
    {
        "unix_date": 1698408259,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:05:21"
    },
    {
        "unix_date": 1698408244,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 13:05:05"
    },
    {
        "unix_date": 1698408167,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:03:48"
    },
    {
        "unix_date": 1698408147,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:03:28"
    },
    {
        "unix_date": 1698408081,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 13:02:23"
    },
    {
        "unix_date": 1698408066,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 13:02:07"
    },
    {
        "unix_date": 1698408060,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 13:02:01"
    },
    {
        "unix_date": 1698408049,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:01:50"
    },
    {
        "unix_date": 1698408049,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:01:51"
    },
    {
        "unix_date": 1698408022,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 13:01:23"
    },
    {
        "unix_date": 1698408016,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:01:18"
    },
    {
        "unix_date": 1698408014,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:01:16"
    },
    {
        "unix_date": 1698407996,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 13:00:57"
    },
    {
        "unix_date": 1698407995,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:00:57"
    },
    {
        "unix_date": 1698407965,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:00:26"
    },
    {
        "unix_date": 1698407953,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:00:14"
    },
    {
        "unix_date": 1698407942,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 13:00:04"
    },
    {
        "unix_date": 1698407921,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:59:42"
    },
    {
        "unix_date": 1698407898,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:59:19"
    },
    {
        "unix_date": 1698407871,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:58:52"
    },
    {
        "unix_date": 1698407868,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:58:50"
    },
    {
        "unix_date": 1698407859,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:58:41"
    },
    {
        "unix_date": 1698407853,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:58:34"
    },
    {
        "unix_date": 1698407842,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:58:23"
    },
    {
        "unix_date": 1698407838,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:58:19"
    },
    {
        "unix_date": 1698407837,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:58:19"
    },
    {
        "unix_date": 1698407837,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:58:19"
    },
    {
        "unix_date": 1698407836,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:58:17"
    },
    {
        "unix_date": 1698407835,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:58:17"
    },
    {
        "unix_date": 1698407810,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:57:51"
    },
    {
        "unix_date": 1698407682,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:55:43"
    },
    {
        "unix_date": 1698407551,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:53:32"
    },
    {
        "unix_date": 1698407467,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 12:52:09"
    },
    {
        "unix_date": 1698407455,
        "Price": 119000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 12:51:57"
    },
    {
        "unix_date": 1698407411,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 12:51:12"
    },
    {
        "unix_date": 1698407379,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 12:50:40"
    },
    {
        "unix_date": 1698407377,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:50:38"
    },
    {
        "unix_date": 1698407344,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:50:06"
    },
    {
        "unix_date": 1698407340,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:50:01"
    },
    {
        "unix_date": 1698407331,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:49:52"
    },
    {
        "unix_date": 1698407324,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:49:46"
    },
    {
        "unix_date": 1698407318,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:49:40"
    },
    {
        "unix_date": 1698407318,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:49:40"
    },
    {
        "unix_date": 1698407318,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:49:40"
    },
    {
        "unix_date": 1698407311,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:49:32"
    },
    {
        "unix_date": 1698407306,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:49:27"
    },
    {
        "unix_date": 1698407252,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:48:33"
    },
    {
        "unix_date": 1698407245,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:48:26"
    },
    {
        "unix_date": 1698407245,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 12:48:27"
    },
    {
        "unix_date": 1698407242,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:48:23"
    },
    {
        "unix_date": 1698407242,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:48:23"
    },
    {
        "unix_date": 1698407239,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:48:20"
    },
    {
        "unix_date": 1698407235,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:48:16"
    },
    {
        "unix_date": 1698407163,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:47:05"
    },
    {
        "unix_date": 1698407148,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:46:49"
    },
    {
        "unix_date": 1698407146,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:46:48"
    },
    {
        "unix_date": 1698407129,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:46:30"
    },
    {
        "unix_date": 1698407126,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:46:27"
    },
    {
        "unix_date": 1698407126,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:46:27"
    },
    {
        "unix_date": 1698407113,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:46:15"
    },
    {
        "unix_date": 1698407112,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:46:13"
    },
    {
        "unix_date": 1698407112,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:46:13"
    },
    {
        "unix_date": 1698407110,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:46:12"
    },
    {
        "unix_date": 1698407109,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:46:10"
    },
    {
        "unix_date": 1698407108,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:46:09"
    },
    {
        "unix_date": 1698407108,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:46:09"
    },
    {
        "unix_date": 1698407108,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:46:09"
    },
    {
        "unix_date": 1698407105,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:46:06"
    },
    {
        "unix_date": 1698407103,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:46:04"
    },
    {
        "unix_date": 1698407102,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:46:05"
    },
    {
        "unix_date": 1698407100,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:46:03"
    },
    {
        "unix_date": 1698407087,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:45:49"
    },
    {
        "unix_date": 1698407086,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:45:47"
    },
    {
        "unix_date": 1698407079,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:45:40"
    },
    {
        "unix_date": 1698407076,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:45:37"
    },
    {
        "unix_date": 1698407073,
        "Price": 0,
        "BIN": 122000,
        "status": "expired",
        "updated": "2023-10-27 12:45:34"
    },
    {
        "unix_date": 1698407070,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:45:32"
    },
    {
        "unix_date": 1698407070,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:45:32"
    },
    {
        "unix_date": 1698407066,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:45:27"
    },
    {
        "unix_date": 1698407059,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:45:20"
    },
    {
        "unix_date": 1698406906,
        "Price": 0,
        "BIN": 122000,
        "status": "expired",
        "updated": "2023-10-27 12:42:48"
    },
    {
        "unix_date": 1698406885,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:42:26"
    },
    {
        "unix_date": 1698406841,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:41:42"
    },
    {
        "unix_date": 1698406841,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:41:43"
    },
    {
        "unix_date": 1698406840,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:41:41"
    },
    {
        "unix_date": 1698406839,
        "Price": 0,
        "BIN": 122000,
        "status": "expired",
        "updated": "2023-10-27 12:41:40"
    },
    {
        "unix_date": 1698406839,
        "Price": 0,
        "BIN": 122000,
        "status": "expired",
        "updated": "2023-10-27 12:41:40"
    },
    {
        "unix_date": 1698406837,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:41:38"
    },
    {
        "unix_date": 1698406837,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:41:38"
    },
    {
        "unix_date": 1698406837,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:41:38"
    },
    {
        "unix_date": 1698406824,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:41:25"
    },
    {
        "unix_date": 1698406734,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:39:56"
    },
    {
        "unix_date": 1698406712,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:39:34"
    },
    {
        "unix_date": 1698406632,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 12:38:13"
    },
    {
        "unix_date": 1698406604,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 12:37:46"
    },
    {
        "unix_date": 1698406594,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:37:35"
    },
    {
        "unix_date": 1698406592,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:37:33"
    },
    {
        "unix_date": 1698406580,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:37:22"
    },
    {
        "unix_date": 1698406577,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:37:18"
    },
    {
        "unix_date": 1698406577,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 12:37:19"
    },
    {
        "unix_date": 1698406574,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 12:37:16"
    },
    {
        "unix_date": 1698406555,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:36:56"
    },
    {
        "unix_date": 1698406551,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:36:53"
    },
    {
        "unix_date": 1698406551,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:36:53"
    },
    {
        "unix_date": 1698406550,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:36:51"
    },
    {
        "unix_date": 1698406540,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:36:41"
    },
    {
        "unix_date": 1698406540,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 12:36:42"
    },
    {
        "unix_date": 1698406539,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:36:41"
    },
    {
        "unix_date": 1698406530,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:36:33"
    },
    {
        "unix_date": 1698406527,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:36:30"
    },
    {
        "unix_date": 1698406524,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 12:36:26"
    },
    {
        "unix_date": 1698406523,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 12:36:24"
    },
    {
        "unix_date": 1698406464,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:35:26"
    },
    {
        "unix_date": 1698406457,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 12:35:18"
    },
    {
        "unix_date": 1698406451,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:35:14"
    },
    {
        "unix_date": 1698406444,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:35:07"
    },
    {
        "unix_date": 1698406436,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:34:57"
    },
    {
        "unix_date": 1698406432,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 12:34:54"
    },
    {
        "unix_date": 1698406385,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:34:06"
    },
    {
        "unix_date": 1698406372,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 12:33:54"
    },
    {
        "unix_date": 1698406371,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:33:53"
    },
    {
        "unix_date": 1698406358,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:33:40"
    },
    {
        "unix_date": 1698406338,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:33:19"
    },
    {
        "unix_date": 1698406336,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:33:17"
    },
    {
        "unix_date": 1698406299,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 12:32:41"
    },
    {
        "unix_date": 1698406283,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:32:24"
    },
    {
        "unix_date": 1698406267,
        "Price": 123000,
        "BIN": 123000,
        "status": "closed",
        "updated": "2023-10-27 12:32:10"
    },
    {
        "unix_date": 1698406239,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:31:43"
    },
    {
        "unix_date": 1698406239,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:31:42"
    },
    {
        "unix_date": 1698406237,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:31:39"
    },
    {
        "unix_date": 1698406231,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 12:31:32"
    },
    {
        "unix_date": 1698406194,
        "Price": 125000,
        "BIN": 125000,
        "status": "closed",
        "updated": "2023-10-27 12:30:55"
    },
    {
        "unix_date": 1698406098,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:29:20"
    },
    {
        "unix_date": 1698406093,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:29:14"
    },
    {
        "unix_date": 1698406092,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:29:13"
    },
    {
        "unix_date": 1698406091,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:29:12"
    },
    {
        "unix_date": 1698406089,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:29:10"
    },
    {
        "unix_date": 1698406087,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:29:08"
    },
    {
        "unix_date": 1698406082,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:29:04"
    },
    {
        "unix_date": 1698406079,
        "Price": 0,
        "BIN": 123000,
        "status": "expired",
        "updated": "2023-10-27 12:29:01"
    },
    {
        "unix_date": 1698406069,
        "Price": 0,
        "BIN": 124000,
        "status": "expired",
        "updated": "2023-10-27 12:28:51"
    },
    {
        "unix_date": 1698406031,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:28:13"
    },
    {
        "unix_date": 1698405839,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 12:25:00"
    },
    {
        "unix_date": 1698405831,
        "Price": 119000,
        "BIN": 119000,
        "status": "closed",
        "updated": "2023-10-27 12:24:52"
    },
    {
        "unix_date": 1698405821,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:24:43"
    },
    {
        "unix_date": 1698405820,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:24:41"
    },
    {
        "unix_date": 1698405795,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:17"
    },
    {
        "unix_date": 1698405794,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:16"
    },
    {
        "unix_date": 1698405793,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:15"
    },
    {
        "unix_date": 1698405792,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:14"
    },
    {
        "unix_date": 1698405792,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:14"
    },
    {
        "unix_date": 1698405792,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:14"
    },
    {
        "unix_date": 1698405791,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:13"
    },
    {
        "unix_date": 1698405791,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:12"
    },
    {
        "unix_date": 1698405790,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:12"
    },
    {
        "unix_date": 1698405790,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:12"
    },
    {
        "unix_date": 1698405789,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:11"
    },
    {
        "unix_date": 1698405789,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:11"
    },
    {
        "unix_date": 1698405788,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:10"
    },
    {
        "unix_date": 1698405788,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:10"
    },
    {
        "unix_date": 1698405788,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:10"
    },
    {
        "unix_date": 1698405788,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:10"
    },
    {
        "unix_date": 1698405787,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:08"
    },
    {
        "unix_date": 1698405787,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:08"
    },
    {
        "unix_date": 1698405787,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:08"
    },
    {
        "unix_date": 1698405785,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:07"
    },
    {
        "unix_date": 1698405785,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:24:08"
    },
    {
        "unix_date": 1698405755,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:23:36"
    },
    {
        "unix_date": 1698405700,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:22:42"
    },
    {
        "unix_date": 1698405693,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:22:35"
    },
    {
        "unix_date": 1698405690,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:22:31"
    },
    {
        "unix_date": 1698405678,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:22:19"
    },
    {
        "unix_date": 1698405631,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:21:33"
    },
    {
        "unix_date": 1698405612,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:21:14"
    },
    {
        "unix_date": 1698405611,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:21:13"
    },
    {
        "unix_date": 1698405603,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:21:05"
    },
    {
        "unix_date": 1698405602,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:21:04"
    },
    {
        "unix_date": 1698405600,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:21:02"
    },
    {
        "unix_date": 1698405597,
        "Price": 122000,
        "BIN": 122000,
        "status": "closed",
        "updated": "2023-10-27 12:20:59"
    },
    {
        "unix_date": 1698405583,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:20:44"
    },
    {
        "unix_date": 1698405577,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:20:38"
    },
    {
        "unix_date": 1698405560,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:20:21"
    },
    {
        "unix_date": 1698405526,
        "Price": 124000,
        "BIN": 124000,
        "status": "closed",
        "updated": "2023-10-27 12:19:48"
    },
    {
        "unix_date": 1698405497,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:19:19"
    },
    {
        "unix_date": 1698405487,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:19:09"
    },
    {
        "unix_date": 1698405484,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:19:05"
    },
    {
        "unix_date": 1698405484,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:19:06"
    },
    {
        "unix_date": 1698405476,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:18:57"
    },
    {
        "unix_date": 1698405475,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:18:56"
    },
    {
        "unix_date": 1698405472,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:18:54"
    },
    {
        "unix_date": 1698405471,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:18:52"
    },
    {
        "unix_date": 1698405470,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:18:52"
    },
    {
        "unix_date": 1698405468,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:18:50"
    },
    {
        "unix_date": 1698405458,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:18:40"
    },
    {
        "unix_date": 1698405450,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:18:32"
    },
    {
        "unix_date": 1698405444,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:18:25"
    },
    {
        "unix_date": 1698405437,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:18:18"
    },
    {
        "unix_date": 1698405431,
        "Price": 120000,
        "BIN": 120000,
        "status": "closed",
        "updated": "2023-10-27 12:18:12"
    },
    {
        "unix_date": 1698405426,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:18:07"
    },
    {
        "unix_date": 1698405425,
        "Price": 0,
        "BIN": 125000,
        "status": "expired",
        "updated": "2023-10-27 12:18:06"
    },
    {
        "unix_date": 1698405406,
        "Price": 121000,
        "BIN": 121000,
        "status": "closed",
        "updated": "2023-10-27 12:17:47"
    }
]';
    }
}
