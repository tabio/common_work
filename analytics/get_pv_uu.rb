#!/usr/bin/env ruby
# coding: utf-8

#---------------------------------------------------------------------------------
# Summary   : Get monthly PV or UU from google analytics api
# Version   : 1.0
# Auther    : tabio <tabio.github@gmail.com>
# Copyright : Copyright (c) 2015 tabio All rights reserved.
# Licence   : http://opensource.org/licenses/bsd-license.php New BSD License
#
# Caution ! : ruby-1.9.3 only
#
#---------------------------------------------------------------------------------

require 'rubygems'
require 'google/api_client'
require 'pp'
require 'nokogiri'
require 'fileutils'
require 'date'

#=== define
SLEEP_INTERVAL  = 60
LOOP_COUNT      = 30
PROFILE_DEFAULT = 'ga:xxxxxx' # profile ID
MAX_RESULT      = 10000
AUTH_CLIENT_ID  = 'xxxxxxxxxxx.apps.googleusercontent.com' # Client ID @Google APIs Console
AUTH_SECRET     = 'hogehoge'                               # Client secret @Google APIs Console
AUTH_ACCESS_TOKEN  = 'hogehoge'                            # access_token of ~/.google-api.yaml
AUTH_REFRESH_TOKEN = 'hogehoge'                            # refresh_token of ~/.google-api.yam 

#=== GoogleAnalytics
module Actindi
  module Google
    class Analytics

      attr_accessor :profile

      def initialize
        @profile     = PROFILE_DEFAULT
        @max_results = MAX_RESULT

        @client = ::Google::APIClient.new(:authorization => :oauth_2, :application_name => 'test application', :application_version => '1.1.0')
        @client.authorization.scope         = 'https://www.googleapis.com/auth/analytics.readonly'
        @client.authorization.client_id     = AUTH_CLIENT_ID
        @client.authorization.client_secret = AUTH_SECRET
        @client.authorization.access_token  = AUTH_ACCESS_TOKEN
        @client.authorization.refresh_token = AUTH_REFRESH_TOKEN
        @client.authorization.fetch_access_token!

        @analytics = @client.discovered_api('analytics', 'v3')
      end

      def page_view(type, path, from, to, start_index = 1)
        if type == "1"
          metrics = 'ga:pageviews'
          sort    = '-ga:pageviews'
        elsif type == "2"
          metrics = 'ga:uniquePageviews'
          sort    = '-ga:uniquePageviews'
        end

        result = @client.execute(:api_method => @analytics.data.ga.get,
                                 :parameters => {
                                   'ids'         => @profile,
                                   'start-date'  => from,
                                   'end-date'    => to,
                                   'metrics'     => metrics,
                                   'dimensions'  => 'ga:networkDomain',
                                   'filters'     => "ga:pagePath=~#{path}",
                                   'sort'        => sort,
                                   'start-index' => start_index,
                                   'max-results' => @max_results
                                 })
        if result.status != 200
          raise result.response.body.to_s
        end
        hash = result.data.rows.inject(Hash.new(0)) do |acc, x|
          acc[x[0]] = x[1].to_i
          acc
        end
        if result.data.rows.size >= @max_results
          hash.merge!(page_view(type, path, from, to, start_index + @max_results))
        end
        hash
      end
    end
  end
end

class ChkAccess
  def main(fpath, date)
    begin
      fpath_w = fpath + "_" + date.to_s
      from = Date.new(date[0..3].to_i, date[4..5].to_i).to_s
      to   = Date.new(date[0..3].to_i, date[4..5].to_i, -1).to_s
      
      open(fpath_w, 'w'){|fw|
        File.open(fpath){|f|
          f.each_line {|pattern|
            count = 0
            pattern.strip!
            unless pattern =~ /^#/
              # 1 : page view
              # 2 : unique page view
              res = Actindi::Google::Analytics.new.page_view('1', pattern, from, to)
              unless res.empty?
                res.each{|k, v|
                  count += v
                }
              end
              fw.write pattern + "\t" + count.to_s + "\n"
            else
              fw.write pattern + "\n"
            end
          }
        }
      }

    rescue => e
      puts e.message
      exit
    end
  end
end

#===> program start
begin
  # check duplicate starting
  lockFile = './file.lock'
  lcount   = 0
  FileUtils.touch(lockFile) unless File.exist?(lockFile)
  f = open(lockFile, "r+")
  loop do
    break if f.flock(File::LOCK_EX | File::LOCK_NB)
    puts "Loop : #{lcount}"
    sleep SLEEP_INTERVAL
    if lcount == LOOP_COUNT
      raise "PROGRAM LOCKED"
    end
    lcount += 1
  end

  # check arguments
  raise 'wrong args number' unless ARGV.size == 2
  raise 'no file exist'     unless File.exist?(ARGV[0])
  raise 'wrong date'        if  !(ARGV[1] =~ /^2015(0[1-9]|1[0-2])$/) || Time.now.strftime("%Y%m") < ARGV[1]

  obj = ChkAccess.new
  obj.main(ARGV[0], ARGV[1])

rescue => e
  p e.message
  puts "ruby 1.9.3 only"
  puts "ruby " + __FILE__ + " [file name] [yyyymm]"
end
#===> program end

