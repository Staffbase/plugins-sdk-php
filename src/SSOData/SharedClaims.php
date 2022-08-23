<?php

interface SharedClaims {
	function hasClaim(string $claim): bool;
	function getClaim(string $claim);
	function getClaimSafe(string $name);
	function getAudience(): ?string;
	function getExpireAtTime(): ?DateTimeImmutable;
	function getNotBeforeTime(): ?DateTimeImmutable;
	function getIssuedAtTime(): ?DateTimeImmutable;
	function getIssuer(): ?string;
	function getId(): ?string;
	function getSubject(): ?string;
	function getRole(): ?string;
	function getData(): array;
}
