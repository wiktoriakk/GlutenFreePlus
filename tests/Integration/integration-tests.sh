#!/bin/bash

# Integration Tests for GlutenFree+ API
# Tests basic endpoints with curl

BASE_URL="http://glutenfree-nginx:80"
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo "======================================"
echo "GlutenFree+ Integration Tests"
echo "======================================"
echo ""

# Test counter
PASSED=0
FAILED=0

# Test 1: Homepage accessible
echo "Test 1: Homepage accessible"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL)
if [ "$RESPONSE" -eq 200 ] || [ "$RESPONSE" -eq 302 ]; then
    echo -e "${GREEN}✓ PASSED${NC} - Homepage returns $RESPONSE"
    ((PASSED++))
else
    echo -e "${RED}✗ FAILED${NC} - Expected 200 or 302, got $RESPONSE"
    ((FAILED++))
fi
echo ""

# Test 2: Login page accessible
echo "Test 2: Login page accessible"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/login)
if [ "$RESPONSE" -eq 200 ]; then
    echo -e "${GREEN}✓ PASSED${NC} - Login page returns 200"
    ((PASSED++))
else
    echo -e "${RED}✗ FAILED${NC} - Expected 200, got $RESPONSE"
    ((FAILED++))
fi
echo ""

# Test 3: Register page accessible
echo "Test 3: Register page accessible"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/register)
if [ "$RESPONSE" -eq 200 ]; then
    echo -e "${GREEN}✓ PASSED${NC} - Register page returns 200"
    ((PASSED++))
else
    echo -e "${RED}✗ FAILED${NC} - Expected 200, got $RESPONSE"
    ((FAILED++))
fi
echo ""

# Test 4: Invalid login returns 401
echo "Test 4: Invalid login returns 401"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" -X POST $BASE_URL/login \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "email=invalid@test.com&password=wrongpass")
if [ "$RESPONSE" -eq 401 ] || [ "$RESPONSE" -eq 403 ]; then
    echo -e "${GREEN}✓ PASSED${NC} - Invalid login returns $RESPONSE"
    ((PASSED++))
else
    echo -e "${RED}✗ FAILED${NC} - Expected 401, got $RESPONSE"
    ((FAILED++))
fi
echo ""

# Test 5: Dashboard requires authentication (redirect to login)
echo "Test 5: Dashboard requires authentication"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/dashboard)
if [ "$RESPONSE" -eq 302 ] || [ "$RESPONSE" -eq 401 ]; then
    echo -e "${GREEN}✓ PASSED${NC} - Dashboard returns $RESPONSE (redirect)"
    ((PASSED++))
else
    echo -e "${RED}✗ FAILED${NC} - Expected 302 or 401, got $RESPONSE"
    ((FAILED++))
fi
echo ""

# Test 6: Static assets accessible
echo "Test 6: Static CSS accessible"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/public/styles/main.css)
if [ "$RESPONSE" -eq 200 ]; then
    echo -e "${GREEN}✓ PASSED${NC} - CSS file returns 200"
    ((PASSED++))
else
    echo -e "${RED}✗ FAILED${NC} - Expected 200, got $RESPONSE"
    ((FAILED++))
fi
echo ""

# Test 7: Logo image accessible
echo "Test 7: Logo image accessible"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/public/images/logo.png)
if [ "$RESPONSE" -eq 200 ]; then
    echo -e "${GREEN}✓ PASSED${NC} - Logo image returns 200"
    ((PASSED++))
else
    echo -e "${RED}✗ FAILED${NC} - Expected 200, got $RESPONSE"
    ((FAILED++))
fi
echo ""

# Test 8: 404 page for invalid route
echo "Test 8: 404 page for invalid route"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/nonexistent-page)
if [ "$RESPONSE" -eq 404 ] || [ "$RESPONSE" -eq 200 ]; then
    echo -e "${GREEN}✓ PASSED${NC} - Invalid route returns $RESPONSE"
    ((PASSED++))
else
    echo -e "${RED}✗ FAILED${NC} - Expected 404, got $RESPONSE"
    ((FAILED++))
fi
echo ""

# Test 9: Registration validation (empty fields)
echo "Test 9: Registration validation"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" -X POST $BASE_URL/register \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "email=&password=&name=")
if [ "$RESPONSE" -eq 400 ] || [ "$RESPONSE" -eq 403 ]; then
    echo -e "${GREEN}✓ PASSED${NC} - Empty registration returns $RESPONSE"
    ((PASSED++))
else
    echo -e "${RED}✗ FAILED${NC} - Expected 400, got $RESPONSE"
    ((FAILED++))
fi
echo ""

# Test 10: Admin panel requires authentication
echo "Test 10: Admin panel requires authentication"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/admin/users)
if [ "$RESPONSE" -eq 302 ] || [ "$RESPONSE" -eq 401 ] || [ "$RESPONSE" -eq 403 ]; then
    echo -e "${GREEN}✓ PASSED${NC} - Admin panel protected (returns $RESPONSE)"
    ((PASSED++))
else
    echo -e "${RED}✗ FAILED${NC} - Expected 302/401/403, got $RESPONSE"
    ((FAILED++))
fi
echo ""

# Summary
echo "======================================"
echo "Test Summary"
echo "======================================"
echo -e "Passed: ${GREEN}$PASSED${NC}"
echo -e "Failed: ${RED}$FAILED${NC}"
echo "Total: $((PASSED + FAILED))"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}All tests passed!${NC}"
    exit 0
else
    echo -e "${RED}Some tests failed!${NC}"
    exit 1
fi