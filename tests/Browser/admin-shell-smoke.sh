#!/usr/bin/env bash
set -euo pipefail

APP_URL="${APP_URL:-http://127.0.0.1:8020}"
ADMIN_USERNAME="${ADMIN_USERNAME:-admin}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-XiguaLocal@2026}"

CURLOPT_CURL="/usr/bin/curl"
CURLOPT_PERL="/usr/bin/perl"
CURLOPT_MKTEMP="/usr/bin/mktemp"

tmpdir="$("$CURLOPT_MKTEMP" -d)"
cleanup() {
  rm -rf "$tmpdir"
}
trap cleanup EXIT

login_page="$("$CURLOPT_CURL" -sS -c "$tmpdir/cookies.txt" "$APP_URL/admin/auth/login")"
token="$(printf '%s' "$login_page" | "$CURLOPT_PERL" -n0e 'print $1 if /name="_token" value="([^"]+)"/')"

if [[ -z "$token" ]]; then
  echo "Failed to parse the admin login token."
  exit 1
fi

"$CURLOPT_CURL" -sS -b "$tmpdir/cookies.txt" -c "$tmpdir/cookies.txt" \
  -D "$tmpdir/login-headers.txt" \
  -o "$tmpdir/login-body.txt" \
  -X POST "$APP_URL/admin/auth/login" \
  --data-urlencode "_token=$token" \
  --data-urlencode "username=$ADMIN_USERNAME" \
  --data-urlencode "password=$ADMIN_PASSWORD" \
  --data-urlencode "remember=1"

login_headers="$(cat "$tmpdir/login-headers.txt")"
login_response="$(cat "$tmpdir/login-body.txt")"

case "$login_headers$login_response" in
  *'"status":true'*'"url":"'"$APP_URL"'/admin"'*|*"Location: $APP_URL/admin"$'\r'*|*"Location: $APP_URL/admin/"$'\r'*|*"location.href = '$APP_URL/admin'"*|*"url='$APP_URL/admin'"*)
    ;;
  *)
    echo "Admin login did not return the expected redirect or success payload."
    echo "$login_headers"
    echo "$login_response"
    exit 1
    ;;
esac

assert_page() {
  local path="$1"
  local expected_title="$2"
  local expected_snippet="${3:-}"
  local html

  html="$("$CURLOPT_CURL" -sS -b "$tmpdir/cookies.txt" -L "$APP_URL$path")"

  case "$html" in
    *"<title>$expected_title</title>"*) ;;
    *)
      echo "Unexpected title for $path"
      echo "Expected: $expected_title"
      exit 1
      ;;
  esac

  if [[ -n "$expected_snippet" ]]; then
    case "$html" in
      *"$expected_snippet"*) ;;
      *)
        echo "Missing smoke snippet for $path: $expected_snippet"
        exit 1
        ;;
    esac
  fi

  printf 'OK %s -> %s\n' "$path" "$expected_title"
}

assert_page "/admin" "后台总览 - 后台壳样板" "今日支付成功率"
assert_page "/admin/v2/dashboard" "后台总览 - 后台壳样板" "今日完成订单"
assert_page "/admin/auth/setting" "账号设置 - 独角数卡西瓜版后台壳" "保存账号设置"
assert_page "/admin/v2/system-setting" "系统设置概览 - 后台壳样板" "编辑订单行为配置"
assert_page "/admin/v2/goods" "商品管理 - 后台壳样板" "商品管理"
assert_page "/admin/v2/goods/create" "新建商品 - 后台壳样板" "创建商品"
assert_page "/admin/v2/goods/create?mode=batch-buy-limit-num" "批量设置限购数量 - 后台壳样板" "批量设置限购数量"
assert_page "/admin/v2/goods/create?mode=batch-sales-volume" "批量设置销量 - 后台壳样板" "目标销量"
assert_page "/admin/v2/goods/create?mode=batch-ord" "批量设置排序 - 后台壳样板" "目标排序"
assert_page "/admin/v2/coupon/batch-ret" "批量设置优惠码可用次数 - 后台壳样板" "目标可用次数"
assert_page "/admin/v2/coupon/batch-code" "批量重生成优惠码内容 - 后台壳样板" "目标前缀"
assert_page "/admin/v2/pay/batch-method" "批量切换支付方式 - 后台壳样板" "目标支付方式"
assert_page "/admin/v2/pay/batch-name" "批量设置支付名称 - 后台壳样板" "目标支付名称"
assert_page "/admin/v2/order/batch-info" "批量设置订单附加信息 - 后台壳样板" "目标附加信息"
assert_page "/admin/v2/order/batch-type" "批量设置订单类型 - 后台壳样板" "目标类型"
assert_page "/admin/v2/order/batch-reset-search-pwd" "批量重置订单查询密码 - 后台壳样板" "批量重置订单查询密码"
assert_page "/admin/v2/emailtpl/create" "新建邮件模板 - 后台壳样板" "创建邮件模板"
assert_page "/admin/v2/order" "订单管理 - 后台壳样板" "订单管理"

echo "Admin shell smoke checks passed."
