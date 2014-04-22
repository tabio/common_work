#!/usr/bin/perl
=head1 NAME
  check_url_status.pl
=head1 SYNOPSIS
  perl check_url_status.pl --file=[file path] --idx=[number] --exists_header --help
  -> idx : target column including url string in list
=head1 DESCRIPTION
  check status of target url
=head1 LICENSE
  Copyright (c) 2014 tabio
  This software is released unser the MIT License.
  http://opensource.org/licenses/mit-license.php
=cut

use strict;
use warnings;
use utf8;
use Encode qw/decode encode/;
use Data::Dumper;
use Getopt::Long;
use LWP::UserAgent;

use constant {
  ERR_MSG    => "<Error> %s\n",
  SLEEP_CNT  => 5,
  HTTP_PROXY => "",
};

my %ini_arr = initial();

#--- set user agenet
my $ua = LWP::UserAgent->new;
if (HTTP_PROXY) { $ua->proxy([qw(http https)], HTTP_PROXY); }
$ua->timeout(SLEEP_CNT);

#--- check file
unless (-f $ini_arr{file}) { die sprintf(ERR_MSG, 'no file'); }
open my $fp,  '<', $ini_arr{file}          || die qq{Can't open file : $!};
open my $fp2, '>', $ini_arr{file} . "_opt" || die qq{Can't open file : $!};

my $i = 0;
while (<$fp>) {
  $i++;

  #--- delete \r \n \r\n (not using chomp)
  s/\x0D?\x0A?$//;

  #--- header check
  if ($i == 1 && $ini_arr{exists_header}) {
    print $fp2 $_."\tstatus\n";
    next;
  }

  #--- decode
  my $line = decode('UTF-8', $_);

  my @tmp = split(/\t/, $line);
  if ($tmp[$ini_arr{idx}] =~ /^http:\/\//) {
     my $response = $ua->get($tmp[$ini_arr{idx}]);
     $line .= "\t".$response->code."\n";
  } else {
     $line .= "\t-\n";
  }
  $line = encode('UTF-8', $line);
  print $fp2 $line;
}

#--- check arguments
sub initial {
  my %opts = ( help => 0, exists_header => 0, idx => 0 );
  GetOptions(\%opts, qw( file=s idx=i exists_header help )) or die sprintf(ERR_MSG, 'unknown option');
  if ($opts{help}) {
    useage();
    exit;
  } elsif (!$opts{file}) {
    useage();
    exit;
  }
  return %opts;
}

#--- check useage
sub useage {
  print "< Usage > perl check_url_status.pl --file=[file path] --idx=[number] --exists_header --help\n";
}

exit;
