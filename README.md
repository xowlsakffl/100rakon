# 백락온 (100rakon)

![백락온 쇼핑몰 화면](https://user-images.githubusercontent.com/50791439/194876606-79a5b9c2-52ae-4b3b-ba15-ee29a8bb3155.PNG)

건강식품의 일반 구매, 정기배송, 연계상품 주문을 하나의 서비스에서 운영하기 위해 개발한 Laravel 기반 쇼핑몰입니다. 상품 탐색과 장바구니부터 주문, Toss Payments 결제, 배송지와 주문 조회, 관리자 백오피스, SMS 알림까지 구매와 운영 흐름 전반을 구현했습니다.

실제 Laravel 애플리케이션은 저장소의 `100rakon_com/` 디렉터리에 있습니다.

## 개발 배경 및 문제

건강식품 판매에는 한 번 구매하는 일반 상품뿐 아니라 이용 기간과 결제 조건을 선택하는 정기배송 상품, 별도의 신청 정보와 비회원 조회가 필요한 연계상품이 함께 존재했습니다. 판매 유형마다 상품 구성, 주문 정보, 배송 조건과 운영 방식이 달라 하나의 단순 주문 구조만으로 처리하기 어려웠습니다.

이에 공통 회원·배송지·운영 기능은 함께 사용하면서도 일반 주문, 정기배송 주문, 연계상품 주문은 각각의 데이터와 처리 흐름으로 분리했습니다. 사용자 구매 화면과 관리자 백오피스는 하나의 Laravel 애플리케이션 안에서 연결해 주문 접수부터 결제 상태 확인과 운영 처리까지 이어지도록 구성했습니다.

## 설계 및 구현

### 1. 판매 유형별 주문 구조

- 일반 상품: 상품 목록과 상세, 장바구니, 바로 주문, 주문 항목과 상태 이력
- 정기배송: 구독 상품과 구성품, 이용 기간, 결제 주기, 시작·종료일, 구독 주문 항목과 이력
- 연계상품: 전용 카테고리와 상품, 별도 주문 정보, 비회원 주문 접수와 조회
- 구매 유형별 모델과 관리자 메뉴를 분리해 서로 다른 상품·주문 규칙을 독립적으로 관리

### 2. 결제와 주문 상태 연결

- Toss Payments 결제 승인 결과를 확인하고 결제 응답을 거래 이력으로 저장
- 결제 성공과 가상계좌 입금 콜백을 일반 주문 또는 정기배송 주문에 반영
- 주문과 주문 항목의 상태를 함께 변경하고 상태 변경 내용을 주문 이력에 기록
- 무통장 입금과 결제 상태 변경 시 Aligo SMS를 발송하고 발송 결과를 별도 저장

### 3. 회원과 구매 경험

- 이메일 인증 기반 회원가입과 로그인
- Kakao·Naver 소셜 로그인
- 상품 탐색, 장바구니, 주문서 작성과 결제 진입
- 회원 배송지 등록·수정·삭제
- 일반 주문과 정기배송 주문 조회, 비회원 연계상품 주문 조회
- 문의 등록과 회원정보 관리

### 4. 관리자 백오피스

- 회원과 배송지 정보 조회
- 일반 상품·카테고리와 이미지 관리
- 정기배송 상품·구성품·카테고리 관리
- 연계상품·카테고리 관리
- 판매 유형별 주문 조회, 검색, 상태 변경과 처리 이력 관리
- 문의 관리와 관리자 메모 기록
- 로그인 여부와 관리자 권한을 확인하는 `check.admin` 미들웨어 적용

## 서비스 흐름

```text
상품 탐색
  ├─ 일반 상품 ─ 장바구니/바로 주문 ─ 일반 주문
  ├─ 정기배송 ─ 기간·결제 조건 선택 ─ 구독 주문
  └─ 연계상품 ─ 신청 정보 입력 ─ 회원/비회원 주문
                         │
                         ▼
             결제 승인 또는 무통장 접수
                         │
                         ▼
          주문 상태·처리 이력·SMS 발송 기록
                         │
                         ▼
            마이페이지 조회 / 관리자 처리
```

## 데이터 모델

| 영역 | 주요 모델 |
| --- | --- |
| 회원 | `User`, `UserAddress` |
| 일반 상품 | `Product`, `ProductCategory` |
| 일반 주문 | `Order`, `OrderItem`, `OrderBasket`, `OrderHistory` |
| 정기배송 | `SubscribGood`, `SubscribGoodProduct`, `SubscribOrder`, `SubscribOrderItem`, `SubscribOrderHistory` |
| 연계상품 | `Outstand`, `OutstandCategory`, `OutstandOrder`, `OutstandItem`, `OutstandHistory` |
| 외부 연동 | `PayTossTransaction`, `SmsSend` |
| 고객지원 | `Qna` |

## 기술 스택

- Backend: PHP 7.3+, Laravel 6, Eloquent ORM
- Database: MySQL
- Frontend: Blade, Sass, JavaScript, Axios, Laravel Mix 4
- Authentication: Laravel Auth, 이메일 인증, Laravel Socialite
- Integration: Toss Payments, Aligo SMS, Kakao OAuth, Naver OAuth

## 주요 라우트

| 영역 | 경로 |
| --- | --- |
| 일반 상품 | `/product`, `/product/{pdx}` |
| 장바구니·주문 | `/order/basket`, `/order`, `/order-save` |
| 정기배송 | `/subscrib`, `/subscrib/{sgdx}`, `/subscrib/order` |
| 연계상품 | `/outstand`, `/outstand/{osdx}`, `/outstand/order` |
| 마이페이지 | `/myorder`, `/myorder-subscrib`, `/myorder-outstand`, `/myaddress` |
| 관리자 | `/admin/*` |

## 실행 방법

```bash
cd 100rakon_com
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run dev
php artisan serve
```

로컬 환경에서 시드 데이터를 생성하면 `demo@100rakon.local` 계정을 사용할 수 있습니다. `APP_ENV=local`일 때 `/_demo/login`으로 접속하면 데모 계정으로 로그인합니다.

결제와 SMS를 실제로 호출하려면 `.env`에 Toss Payments, Aligo, 운영 연락처 관련 값을 설정해야 합니다. 운영 DB와 실제 인증 정보는 저장소에 포함하지 않았습니다.

## 공개 코드 정리

- 누락된 Laravel 실행 구성과 `.env.example` 복구
- 관리자 미인증 접근 오류를 수정하고 권한이 없는 계정은 `403`으로 차단
- 상품 상세의 존재하지 않는 데이터 접근을 `404`로 처리
- Toss 가상계좌 콜백에 secret 검증 추가
- SMS API Key, 운영 연락처와 입금 계좌 정보를 환경변수로 이동
- 잘못된 리소스 파라미터와 중복 관리자 라우트 정리
- 포트폴리오 확인용 시드 데이터와 로컬 데모 로그인 추가

## 현재 코드의 한계와 개선 방향

이 저장소는 당시 Laravel 6 환경에서 구현한 프로젝트를 포트폴리오용으로 정리한 공개본입니다. 현재 기준으로 다시 운영한다면 다음 보완이 필요합니다.

- 요청값의 상품 가격과 주문 총액을 신뢰하지 않고 서버의 상품 데이터로 다시 계산
- 주문, 주문 항목, 처리 이력과 결제 상태 변경을 데이터베이스 트랜잭션으로 묶어 원자성 확보
- 일자별 주문 개수 대신 UUID 또는 유니크 제약이 있는 주문번호 생성 방식 적용
- Toss `paymentKey`와 콜백 이벤트에 유니크 제약 및 멱등성 처리 추가
- 일반·정기배송·연계상품 컨트롤러의 중복 주문 로직을 공통 서비스로 분리
- SMS 같은 외부 호출을 큐로 분리하고 재시도·실패 상태를 관리
- Laravel과 프론트엔드 의존성을 지원 중인 버전으로 업그레이드

## 본인 기여

상품과 판매 유형별 주문 구조 설계, 데이터베이스 모델링, 사용자 구매 화면, Toss Payments와 Aligo SMS 연동, 마이페이지와 관리자 백오피스 개발을 담당했습니다. 일반 구매와 정기배송, 연계상품의 서로 다른 흐름을 하나의 서비스 안에서 운영할 수 있도록 구매부터 운영까지 전 과정을 구현했습니다.
