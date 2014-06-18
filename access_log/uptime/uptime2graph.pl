#!/usr/bin/perl
=head1 NAME
  uptime2graph.pl
=head1 SYNOPSIS
  perl uptime2graph.pl [suffix of target log file]
=head1 DESCRIPTION
  summarize file of uptime format
=head1 LICENSE
  Copyright (c) 2014 tabio
  This software is released unser the MIT License.
  http://opensource.org/licenses/mit-license.php
=cut

use strict;
use warnings;
use utf8;
use File::Basename qw/basename dirname/;
use Data::Dumper;

#========================
# output error message
#========================
sub err_msg {
  my $type = shift || 0;
  if ($type == 0) {
    print "[warning] wrong argument\n";
    print "argument ==> suffix of target log file\n";
    print "(ex.) perl ". __FILE__ ." _uptime.logs\n";
  }
}

if ($#ARGV != 0) {
  err_msg;
} else {

  # get data
  my $data = {};
  my @list = `/bin/find ./* -name *$ARGV[0] -type f`;
  foreach my $file (@list) {
    chomp $file;
    my $prefix = basename $file;
    $prefix    =~ s/$ARGV[0]//;

    open(FH, $file);
    while (<FH>) {
      my @tmp = split(" ", $_);
      $tmp[$#tmp - 2] =~ s/,//;
      $tmp[0]  =~ s/(\d{2}:\d{2})(:\d{2})/$1/;
      $data->{$prefix}->{$tmp[0]} = $tmp[$#tmp - 2];
    }
    close(FH);
  }

  # make list
  my @now = localtime;
  my $file_opt = "list_".$now[2].$now[1].$now[0].".tsv";
  open(FO, "> $file_opt");

  #--- header
  my $header = "time";
  foreach my $key (sort keys %$data) {
    $header .= "\t".$key;
  }
  $header .= "\n";
  print FO $header;

  #--- data
  for(my $h=0; $h<24; $h++) {
    for(my $m=0; $m<60; $m = $m + 5) {
      my $time   = sprintf('%02d', $h).":".sprintf('%02d', $m);
      my $line_t = $time;
      foreach my $key (sort keys %$data) {
        $line_t .= "\t";
        if (defined($data->{$key}->{$time})) {
          $line_t .= $data->{$key}->{$time};
        } else {
          $line_t .= "";
        }
      }
      $line_t .= "\n";
      print FO $line_t;
    }
  }
  close(FO);
}
exit;

